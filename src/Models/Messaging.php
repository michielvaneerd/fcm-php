<?php

namespace Mve\FcmPhp\Models;

use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\LoggerInterface;

class Messaging
{

    use LoggerTrait;

    // All errors are at least composed like this:
    // {
    //     "error": {
    //         "code": 400,
    //         "message": "Fail to resolve resource 'projects/BLAAT'",
    //         "status": "INVALID_ARGUMENT"
    //     }
    // }

    private const ERROR_UNAUTHENTICATED = 'UNAUTHENTICATED';

    // https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode
    private const ERROR_CODES_MESSAGING = [
        'INVALID_ARGUMENT' => [
            'code' => 400,
            'desc' => 'Request parameters were invalid.'
        ],
        'UNREGISTERED' => [
            'code' => 404,
            'desc' => 'App instance was unregistered from FCM.'
        ],
        'SENDER_ID_MISMATCH' => [
            'code' => 403,
            'desc' => 'The authenticated sender ID is different from the sender ID for the registration token.'
        ],
        'QUOTA_EXCEEDED' => [
            'code' => 429,
            'desc' => 'Sending limit exceeded for the message target.'
        ],
        'UNAVAILABLE' => [
            'code' => 503,
            'desc' => 'The server is overloaded.'
        ],
        'INTERNAL' => [
            'code' => 500,
            'desc' => 'An unknown internal error occurred.'
        ],
        'THIRD_PARTY_AUTH_ERROR' => [
            'code' => 401,
            'desc' => 'APNs certificate or web push auth key was invalid or missing.'
        ],

        // This is not named on this page, but will be received when we do a call without a valid bearer token.
        // But these error codes are defined in: https://cloud.google.com/resource-manager/docs/core_errors - which are GLOBAL error codes
        // that are global to the Google API's for many projects.
        'UNAUTHENTICATED' => [
            'code' => 401,
            'desc' => 'The user is not authorized to make the request.'
        ],
        // Also not named on this page, but it seems that this registration token has changed (so the app needs to POST it to the backend)
        'NOT_FOUND' => [
            'code' => 404,
            'desc' => 'The requested operation failed because a resource associated with the request could not be found.'
        ],
        'TOO_MANY_REQUESTS' => [
            'code' => 429,
            'desc' => 'Too many requests have been sent within a given time span.'
        ]
    ];

    // https://developers.google.com/instance-id/reference/server#results_3
    // Apparently we always receive a 200 HTTP status code.
    private const ERROR_CODES_TOPICS = [
        'NOT_FOUND' => 'The registration token has been deleted or the app has been uninstalled.',
        'INVALID_ARGUMENT' => 'The registration token provided is not valid for the Sender ID.',
        'INTERNAL' => 'The backend server failed for unknown reasons. Retry the request.',
        'TOO_MANY_TOPICS' => 'Excessive number of topics per app instance.',
        'RESOURCE_EXHAUSTED' => 'Too many subscription or unsubscription requests in a short period of time. Retry with exponential backoff.'
    ];

    // https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode

    function __construct(
        private AccessTokenHandler $accessTokenHandler,
        private ?LoggerInterface $logger
    ) {
    }

    public function subscribeToTopic(string $token, string $topic): bool
    {
        $client = HttpClient::create();
        $uri = 'https://iid.googleapis.com/iid/v1/' . $token . '/rel/topics/' . $topic;
        try {
            $bearerToken = $this->accessTokenHandler->getToken();
            $response = $client->request('POST', $uri, [
                'auth_bearer' => $bearerToken,
                'json' => [],
                'headers' => [
                    'access_token_auth' => 'true',
                ]
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $ex) {
            // Or do something else?
            throw $ex;
        }
    }

    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        $client = HttpClient::create();
        $uri = 'https://iid.googleapis.com/iid/v1:batchRemove';
        try {
            $bearerToken = $this->accessTokenHandler->getToken();
            $response = $client->request('POST', $uri, [
                'auth_bearer' => $bearerToken,
                'json' => [
                    'to' => $topic,
                    'registration_tokens' => $tokens
                ],
                'headers' => [
                    'access_token_auth' => 'true',
                ]
            ]);
            // See: https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances
            // For each token we receive SUCCESS (empty) or some kind of ERROR code. But the HTTP response will still be 200.
            return $response->getStatusCode() === 200;
        } catch (\Exception $ex) {
            // Or do something else?
            throw $ex;
        }
    }

    /**
     * Send multiple messages to multiple devices.
     * 
     * @param Message[] $messages Messages to send
     * @param SendAllResult $sendAllResult If this is set, then this is the second time this is called from within itself (to force get access token from API).
     * 
     * @return SendAllResult Sent and invalid tokens.
     * 
     * @throws FcmException When we cannot get a valid access token.
     * @throws Exception Other exceptions, like cannot connect to Google API, in this case it is not known if messages have been processed.
     */
    public function sendAll(array $messages, ?SendAllResult $sendAllResult = null): SendAllResult
    {
        $client = HttpClient::create();
        $sendMessagesUri = 'https://fcm.googleapis.com/v1/projects/' . $this->accessTokenHandler->getProjectId() . '/messages:send';

        $responses = [];

        $withForceTokenFromApi = $sendAllResult !== null;
        if ($sendAllResult === null) {
            $sendAllResult = new SendAllResult();
        }

        try {

            // Not sure if we can get an exception here.
            $bearerToken = $this->accessTokenHandler->getToken($withForceTokenFromApi);

            // These are done async and with HTTP/2 multiplexed
            // The responses are lazy
            foreach ($messages as $message) {
                $this->log("Send request to $sendMessagesUri for token {$message->token}");
                $responses[] = $client->request('POST', $sendMessagesUri, [
                    'auth_bearer' => $bearerToken,
                    'json' => $message->toArray()
                ]);
            }

            // Because we loop through the responses in the same order as the requests, we also know the message for this response.
            // Note that this is maybe not the most performant way to loop, because maybe response 2 got here before response 1 making this loop wait for the first response
            for ($i = 0; $i < count($responses); $i++) {

                $response = $responses[$i];
                $message = array_shift($messages);

                $code = $response->getStatusCode();

                if ($code === 200) {
                    $sendAllResult->sentIds[] = $message->id;
                    $this->log("OK response for {$message->token}");
                } else {
                    $content = null;
                    $errorName = null;
                    $rawContent = null;
                    $errorMessage = null;

                    try {
                        // Error responses are JSON with an 'error' key that is an array.
                        $rawContent = $response->getContent(false);
                        $this->log("Error response for {$message->token}: $rawContent");
                        $content = $response->toArray(false);
                        $error = $content['error'];
                        $errorName = $error['status'] ?? null;
                        $errorMessage = $error['message'] ?? null;
                    } catch (\Exception $ex) {
                        // Do nothing, we just catch it in case toArray is called on a response that is not an array,
                        // and in that case we will have the rawContent.
                    }

                    if ($code === 404) {
                        // We can also get 'INVALID_ARGUMENT' and this *can* be an invalid token error, but it can also be a wrongly formatted message,
                        // so never remove the token based on this error.
                        $sendAllResult->invalidIds[] = $message->id;
                    } elseif ($code === 401 && $errorName === self::ERROR_UNAUTHENTICATED) {
                        if ($withForceTokenFromApi) {
                            $sendAllResult->errorIds[$message->id] = new FcmException($errorMessage ?? 'Cannot get access token from API', $code, $content, $errorName, $rawContent);
                            throw new FcmException($errorMessage ?? 'Cannot get access token from API', $code, $content, $errorName, $rawContent, $sendAllResult);
                        }
                        array_unshift($messages, $message); // Put back this last message we removed earlier, because we have to process this one again.
                        return $this->sendAll($messages, $sendAllResult);
                    } else {
                        // We have an unknown error
                        $sendAllResult->errorIds[$message->id] = new FcmException($errorMessage ?? 'Unknown error', $code, $content, $errorName, $rawContent);
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            // See: https://symfony.com/doc/current/http_client.html#canceling-responses
            // With unset() all the destructors of the responses are called, which can throw exceptions.
            try {
                unset($responses);
            } catch (\Exception $ex) {
            }
        }

        return $sendAllResult;
    }

    /*
    ALs je een zelfverzonnen token gebruikt (die voldoet dus niet aan hoe een token eruit ziet):
1 => array:1 [▼
      "error" => array:4 [▼
        "code" => 400
        "message" => "The registration token is not a valid FCM registration token"
        "status" => "INVALID_ARGUMENT"
        "details" => array:1 [▶]
      ]
    ]


    Als je een verkeerde bearer token genbruikt:

    0 => 401
    1 => array:1 [▼
      "error" => array:3 [▼
        "code" => 401
        "message" => "Request had invalid authentication credentials. Expected OAuth 2 access token, login cookie or other valid authentication credential. See https://developers.goo ▶"
        "status" => "UNAUTHENTICATED"
      ]
    ]
  ]

    */
}
