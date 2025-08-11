<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class for sending messages.
 */
class Messaging
{
    private AccessTokenHandler $accessTokenHandler;

    /**
     * @param CacheInterface $cache A CacheInterface implemenation. This will be used to cache the access token.
     * @param string $jsonFile The path to the Google Firebase private key file in JSON format.
     * @param ?LoggerInterface $logger An optional LoggerInterface implemenation.
     */
    function __construct(
        private CacheInterface $cache,
        string $jsonFile,
        private readonly ?LoggerInterface $logger
    ) {
        $this->accessTokenHandler = new AccessTokenHandler($cache, $jsonFile, $logger);
    }

    /**
     * Returns the AccessTokenHandler instance.
     */
    public function getAccessTokenHandler(): AccessTokenHandler
    {
        return $this->accessTokenHandler;
    }

    /**
     * Call the Google API with one retry in case of expired access token.
     * 
     * @param string $uri Google API endpoint.
     * @param string $method Method for API request.
     * @param ?array $json The JSON body.
     * @param ?array $headers The headers.
     * @param bool $retry If this is the retry phase.
     * 
     * @return ResponseInterface The ResponseInterface instance.
     * 
     * @throws FcmClientException In case we cannot generate a JWT token. This is always fatal and we cannot fix this without making changes to the code or to the downloaded service account private key.
     * @throws FcmException In case of a Google API error, like an invalid request for the access token.
     * @throws \Exception In case of some other error.
     */
    private function callWithRetryOnExpiredAccessToken(
        string $uri,
        string $method = 'POST',
        ?array $json = null,
        ?array $headers = null,
        bool $retry = false
    ): ResponseInterface {
        $client = HttpClient::create();
        $bearerToken = $this->accessTokenHandler->getToken($retry);
        $options = [
            'auth_bearer' => $bearerToken,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];
        if (!empty($headers)) {
            $options['headers'] = $options['headers'] + $headers;
        }
        if (!empty($json)) {
            $options['json'] = $json;
        }
        $response = $client->request($method, $uri, $options);
        $statusCode = $response->getStatusCode();
        $this->logger->debug("Response for $method request to $uri with status $statusCode: " . $response->getContent(false));
        if ($statusCode === 401) {
            if ($retry) {
                $error = FcmError::ERROR_UNAUTHENTICATED;
                $message = FcmError::ERROR_UNAUTHENTICATED;
                try {
                    $jsonResponse = $response->toArray(false);
                    if (!empty($jsonResponse['error'])) {
                        if (!empty($jsonResponse['error']['status'])) {
                            $error = $jsonResponse['error']['status'];
                        }
                        if (!empty($jsonResponse['error']['message'])) {
                            $message = $jsonResponse['error']['message'];
                        }
                    }
                } catch (\Exception $ex) {
                    // Do nothing, because we know that some responses are not in JSON format.
                }
                throw new FcmException(new FcmError($statusCode, $error, $message, $response->getContent(false)));
            } else {
                return $this->callWithRetryOnExpiredAccessToken($uri, $method, $json, $headers, true);
            }
        }
        return $response;
    }

    /**
     * Gets info about a specific token.
     * 
     * @param string $token The registration token.
     * @param string $details If we want more details for this token, like the topics for this token.
     * 
     * @return array JSON response.
     * 
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://developers.google.com/instance-id/reference/server#get_information_about_app_instances
     */
    public function getInfo(string $token, bool $details = false): array
    {
        $uri = 'https://iid.googleapis.com/iid/info/' . $token;
        if ($details) {
            $uri .= "?details=true";
        }
        $headers = [
            'access_token_auth' => 'true',
        ];
        $response = $this->callWithRetryOnExpiredAccessToken($uri, 'GET', null, $headers);
        $code = $response->getStatusCode();
        $json = $response->toArray(false);
        if ($code === 200) {
            return $json;
        } else {
            throw new FcmException(new FcmError($code, $json['error'], $json['message'] ?? '', $response->getContent(false)));
        }
    }

    /**
     * Sends a message to a topic
     * 
     * @param TopicMessage $topicMessage The message to send.
     * 
     * @return bool True if we got a 200 status code.
     * 
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-topics
     */
    public function sendToTopic(TopicMessage $topicMessage): bool
    {
        $uri = 'https://fcm.googleapis.com/v1/projects/' . $this->accessTokenHandler->getProjectId() . '/messages:send';
        $response = $this->callWithRetryOnExpiredAccessToken($uri, 'POST', $topicMessage->toArray(), null);
        return $response->getStatusCode() === 200;
    }

    /**
     * Subscribe token to topic.
     * 
     * @param string $token The registration token.
     * @param string $topic The topic.
     * 
     * @return bool True if we got a 200 status code.
     * 
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://developers.google.com/instance-id/reference/server#create_a_relation_mapping_for_an_app_instance
     */
    public function subscribeToTopic(string $token, string $topic): bool
    {
        $uri = 'https://iid.googleapis.com/iid/v1/' . $token . '/rel/topics/' . $topic;
        $response = $this->callWithRetryOnExpiredAccessToken($uri, 'POST', null, ['access_token_auth' => 'true']);
        return $response->getStatusCode() === 200;
    }

    /**
     * Unsubscribe tokens from topic.
     * 
     * @param array $tokens Array with tokens.
     * @param string $topic Topic name.
     * 
     * @return bool True if we got a 200 status code.
     * 
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        // See: https://developers.google.com/instance-id/reference/server#manage_relationship_maps_for_multiple_app_instances
        // TODO: For each token we receive SUCCESS (empty) or some kind of ERROR code. But the HTTP response will still be 200.
        $uri = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $json = [
            'to' => "/topics/$topic",
            'registration_tokens' => $tokens
        ];
        $response = $this->callWithRetryOnExpiredAccessToken($uri, 'POST', $json, ['access_token_auth' => 'true']);
        return $response->getStatusCode() === 200;
    }

    /**
     * Send multiple messages.
     * 
     * @param array<TokenMessage> $tokenMessages A list of messages to send.
     * 
     * @return SendAllResult A SendAllResult result, which contains the sent, unregistered and error
     * 
     * @throws FcmClientException In case we cannot generate a JWT token.
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-multiple-devices
     */
    public function sendAll(array $tokenMessages): SendAllResult
    {
        return $this->_sendAll($tokenMessages);
    }

    /**
     * Validate multiple messages. Can be used to check if tokens are still registered.
     * 
     * @param array<TokenMessage> $tokenMessages A list of messages to validate.
     * 
     * @return SendAllResult A SendAllResult result, which contains the sent, unregistered and error
     * 
     * @throws FcmClientException In case we cannot generate a JWT token.
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-multiple-devices
     * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages/send
     */
    public function validateAll(array $tokenMessages): SendAllResult
    {
        return $this->_sendAll($tokenMessages, true);
    }

    /**
     * Send multiple messages with an optional retry in case of expired access token.
     * 
     * @param array<TokenMessage> $tokenMessages A list of messages to send.
     * @param ?SendAllResult $sendAllResult The SendAllResult instance (this is only defined if this is a retry in case of expired access token).
     * 
     * @return SendAllResult A SendAllResult result, which contains the sent, unregistered and error
     * 
     * @throws FcmClientException In case we cannot generate a JWT token.
     * @throws FcmException If we get a Google error.
     * @throws \Exception For all other errors.
     * 
     * @link https://firebase.google.com/docs/cloud-messaging/send-message#send-messages-to-multiple-devices
     */
    private function _sendAll(array $tokenMessages, bool $validate = false, ?SendAllResult $sendAllResult = null): SendAllResult
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
            foreach ($tokenMessages as $message) {
                $this->logger->debug("Send request to $sendMessagesUri for token {$message->getToken()}");
                $body = $message->toArray();
                if ($validate) {
                    $body['validate_only'] = true;
                }
                $responses[] = $client->request('POST', $sendMessagesUri, [
                    'auth_bearer' => $bearerToken,
                    'json' => $body
                ]);
            }

            // Because we loop through the responses in the same order as the requests, we also know the message for this response.
            // Note that this is maybe not the most performant way to loop, because maybe response 2 got here before response 1 making this loop wait for the first response
            for ($i = 0; $i < count($responses); $i++) {

                $response = $responses[$i];
                $message = array_shift($tokenMessages);

                $code = $response->getStatusCode();
                $content = $response->getContent(false);

                $jsonResponse = null;
                try {
                    $jsonResponse = $response->toArray(false);
                } catch (\Exception $ex) {
                    // Do nothing.
                }

                if ($code === 200) {
                    $sendAllResult->addToSent($message->getId(), $jsonResponse['name']);
                } else {
                    $error = $jsonResponse['error'] ?? [];
                    $errorStatus = $error['status'] ?? 'FCM_MISSING_ERROR';
                    $errorMessage = $error['message'] ?? 'FCM_MISSING_MESSAGE';
                    $fcmError = new FcmError($code, $errorStatus, $errorMessage, $content);

                    if ($code === 404 && in_array($errorStatus, [FcmError::ERROR_UNREGISTERED, FcmError::ERROR_NOT_FOUND])) {
                        // These are tokens that have been unregistered and can safely be removed.
                        $sendAllResult->addToUnregistered($message->getId(), $fcmError);
                    } elseif ($code === 401 && $errorStatus === FcmError::ERROR_UNAUTHENTICATED) {
                        // This means we have an expired access token, so try again but this time get a new access token from the Google API instead
                        // of using the one from the cache.
                        if ($withForceTokenFromApi) {
                            // If this is the second time we are here, exit with an exception.
                            // This is serious, because this means we cannot get an access token from the Google API.
                            $sendAllResult->addToErrors($message->getId(), $fcmError);
                            throw new FcmException($fcmError, $sendAllResult);
                        }
                        array_unshift($tokenMessages, $message); // Put back this last message we removed earlier, because we have to process this one again.
                        return $this->_sendAll($tokenMessages, $validate, $sendAllResult);
                    } else {
                        $this->logger->warning("Unknown API response with statuscode $code for {$message->getToken()}: " . $content);
                        $sendAllResult->addToErrors($message->getId(), $fcmError);
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
}
