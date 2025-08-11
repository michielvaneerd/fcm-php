<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

//use Google\Auth\ApplicationDefaultCredentials;
//use Google\Auth\FetchAuthTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Manages the access token that is needed to authenticate the Google Firebase API calls.
 */
class AccessTokenHandler
{
    //private FetchAuthTokenInterface $fetchAuthToken;
    private array $serviceAccount;

    public const CACHE_ACCESS_TOKEN_NAME = 'mve_fcm_php_token';

    const SCOPE_FIREBASE_MESSAGING = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * @param CacheInterface $cache A CacheInterface implementation, used to cache the access token.
     * @param string $jsonFile The Google private key JSON file.
     * @param ?LoggerInterface $logger An optional LoggerInterface implementation.
     */
    function __construct(
        private CacheInterface $cache,
        string $jsonFile,
        private readonly ?LoggerInterface $logger
    ) {
        $this->serviceAccount = json_decode(file_get_contents($jsonFile), true);
        // Below is using the Google library so disabled for now.
        //putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $jsonFile);
        //this->fetchAuthToken = ApplicationDefaultCredentials::getCredentials(self::SCOPE_FIREBASE_MESSAGING);
    }

    // This makes use of the Google API library, so disabled for now as below we do this on our own.
    // /**
    //  * Returns a non expired access token from the cache, or if there isn't one, from the Google API and stores this one in the cache.
    //  * 
    //  * @param bool $forceFromApi If true, then it gets the access token from the Google API always, even there is still one in the cache.
    //  * 
    //  * @return string A non expired access token that can be used to authenticate the API calls.
    //  */
    // public function getTokenWithGoogleAPI(bool $forceFromApi = false): string
    // {
    //     $token = $forceFromApi ? null : $this->cache->get(self::CACHE_ACCESS_TOKEN_NAME);
    //     if (empty($token)) {
    //         if ($forceFromApi) {
    //             $this->logger->debug('No access token and forceFromApi, now try the Google API...');
    //         } else {
    //             $this->logger->debug('No access token, now try the Google API...');
    //         }
    //         $response = $this->fetchAuthToken->fetchAuthToken();
    //         $token = $response['access_token'];
    //         $this->logger->debug('Access token from Google API: ' . $token); // Note you should have DISABLED 'debug' log level in production or test!
    //         // We subtract 30 seconds from the TTL to make sure we have a valid one and can be used for requesting a batch of calls (that can take some time).
    //         // Currently access tokens are valid for 1 hour. The 'expires_in' is in seconds.
    //         $this->cache->put(self::CACHE_ACCESS_TOKEN_NAME, $token, $response['expires_in'] - 30);
    //     }
    //     $this->logger->debug('Access token: ' . $token);
    //     return $token;
    // }

    /**
     * Returns a non expired access token from the cache, or if there isn't one, from the Google API and stores this one in the cache.
     * 
     * @param bool $forceFromApi If true, then it gets the access token from the Google API always, even there is still one in the cache.
     * 
     * @return string A non expired access token that can be used to authenticate the API calls.
     * 
     * @throws FcmClientException In case we cannot generate a JWT token.
     * @throws FcmException In case the Google API returns a valid error JSON response.
     * @throws \Exception In case of another error.
     */
    public function getToken(bool $forceFromApi = false): string
    {
        $token = $forceFromApi ? null : $this->cache->get(self::CACHE_ACCESS_TOKEN_NAME);
        if (empty($token)) {

            $response = $this->fetchTokenFromGoogleAPI();

            $token = $response['access_token'];

            $this->logger->debug('Got access token from Google API: ' . $token); // Note you should have DISABLED 'debug' log level in production or test!
            // We subtract 30 seconds from the TTL to make sure we have a valid one and can be used for requesting a batch of calls (that can take some time).
            // Currently access tokens are valid for 1 hour. The 'expires_in' is in seconds.
            $this->cache->put(self::CACHE_ACCESS_TOKEN_NAME, $token, $response['expires_in'] - 30);
        }
        return $token;
    }

    /**
     * Requests an access token from the Google API.
     * 
     * @return array Array of the response. Contains the 'access_token' and 'expires_in' fields.
     * 
     * @throws FcmClientException In case we cannot generate a JWT token.
     * @throws FcmException When we don't get a 200 status code back.
     * @throws \Exception In case of some other error, for example if the server doesn't return valid JSON.
     */
    private function fetchTokenFromGoogleAPI(): array
    {
        $jwt = $this->generateJWT();
        $uri = 'https://oauth2.googleapis.com/token';
        $client = HttpClient::create();
        $response = $client->request('POST', $uri, [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]
        ]);
        $code = $response->getStatusCode();
        $content = $response->getContent(false);
        $json = $response->toArray(false);
        if (array_key_exists('error', $json)) {
            // See: https://developers.google.com/identity/protocols/oauth2/service-account#error-codes
            $this->logger->error($content);
            throw new FcmException(new FcmError($code, $json['error'], $json['error_description'], $content));
        }
        return $json;
    }

    /**
     * Returns the project ID.
     */
    public function getProjectId(): string
    {
        return $this->serviceAccount['project_id'];
    }

    /**
     * Returns a base64 encoded URL safe string.
     */
    private function base64EncodeUrl(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Generates a JWT token that we need to request an access token.
     * 
     * @link https://developers.google.com/identity/protocols/oauth2/service-account
     * @link https://stackoverflow.com/questions/74192530/generating-access-token-for-firebase-messaging/75591963#75591963
     * 
     * @throws FcmClientException In case we cannot sign the token.
     */
    private function generateJWT(): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $this->serviceAccount['private_key_id']
        ];
        $headerEncoded = $this->base64EncodeUrl(json_encode($header));

        $iat = time() - 60;
        $exp = $iat + 3600;
        $claims = [
            'iss' => $this->serviceAccount['client_email'],
            'scope' => self::SCOPE_FIREBASE_MESSAGING,
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $exp,
            'iat' => $iat
        ];
        $claimsEncoded  = $this->base64EncodeUrl(json_encode($claims));

        $toSign = $headerEncoded . '.' . $claimsEncoded;

        $privateKey = openssl_get_privatekey($this->serviceAccount['private_key']);
        if (!openssl_sign($toSign, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new FcmClientException('Cannot sign JWT token', 401);
        }
        $signatureEncoded = $this->base64EncodeUrl($signature);

        $jwt = $toSign . '.' . $signatureEncoded;

        return $jwt;
    }
}
