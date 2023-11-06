<?php

namespace Mve\FcmPhp\Models;

use Symfony\Component\HttpClient\HttpClient;

class Messaging
{
    private AccessTokenHandler $accessTokenHandler;

    // https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode

    function __construct(AccessTokenHandler $accessTokenHandler)
    {
        $this->accessTokenHandler = $accessTokenHandler;
    }

    /**
     * Send multiple messages to multiple devices.
     * 
     * @param Message[] $messages Messages to send
     * @param SendResult $sendResult If this is set, then this is the second time this is called from within itself (to force get access token from API).
     * 
     * @return SendResult Sent and invalid tokens.
     * 
     * @throws FcmException When we cannot get a valid access token.
     * @throws Exception Other exceptions, like cannot connect to Google API, in this case it is not known if messages have been processed.
     */
    public function sendAll(array $messages, ?SendResult $sendResult = null): SendResult
    {
        $client = HttpClient::create();
        $sendMessagesUri = 'https://fcm.googleapis.com/v1/projects/' . $this->accessTokenHandler->getProjectId() . '/messages:send';

        $responses = [];

        $withForceTokenFromApi = $sendResult !== null;
        if ($sendResult === null) {
            $sendResult = new SendResult();
        }

        try {

            // Not sure if we can get an exception here.
            $bearerToken = $this->accessTokenHandler->getToken($withForceTokenFromApi);

            // These are done async and with HTTP/2 multiplexed
            // The responses are lazy
            foreach ($messages as $message) {
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
                    $sendResult->sentIds[] = $message->id;
                } else {
                    $content = null;
                    $errorName = null;
                    $rawContent = null;
                    $errorMessage = null;

                    try {
                        // Error responses are JSON with an 'error' key that is an array.
                        $content = $response->toArray(false);
                        $error = $content['error'];
                        $errorName = $error['status'] ?? null;
                        $errorMessage = $error['message'] ?? null;
                    } catch (\Exception $ex) {
                        $rawContent = $response->getContent(false);
                    }

                    if ($code === 400 && $errorName === 'INVALID_ARGUMENT') {
                        $sendResult->invalidIds[] = $message->id;
                    } elseif ($code === 401 && $errorName === 'UNAUTHENTICATED') {
                        if ($withForceTokenFromApi) {
                            $sendResult->errorIds[$message->id] = new FcmException($errorMessage ?? 'Cannot get access token', $code, $content, $errorName, $rawContent);
                            throw new FcmException($errorMessage ?? 'Cannot get access token', $code, $content, $errorName, $rawContent, $sendResult);
                        }
                        array_unshift($messages, $message); // Put back this last message we removed earlier, because we have to process this one again.
                        return $this->sendAll($messages, $sendResult);
                    } else {
                        $sendResult->errorIds[$message->id] = new FcmException($errorMessage ?? 'Unknown error', $code, $content, $errorName, $rawContent);
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        } finally {
            try {
                unset($responses);
            } catch (\Exception $ex) {
            }
        }

        return $sendResult;
    }

    /*
    ALs je een verkeerde token gebruikt:
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
