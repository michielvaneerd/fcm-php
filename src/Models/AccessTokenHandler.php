<?php

declare(strict_types=1);

namespace Mve\FcmPhp\Models;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\FetchAuthTokenInterface;
use Psr\Log\LoggerInterface;

/**
 * Manages the access token that is needed to authenticate the Google Firebase API calls.
 */
class AccessTokenHandler
{
    private FetchAuthTokenInterface $fetchAuthToken;
    private string $projectId;

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
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $jsonFile);
        $json = json_decode(file_get_contents($jsonFile));
        $this->projectId = $json->project_id;
        $this->fetchAuthToken = ApplicationDefaultCredentials::getCredentials(self::SCOPE_FIREBASE_MESSAGING);
    }

    /**
     * Returns a non expired access token from the cache, or if there isn't one, from the Google API and stores this one in the cache.
     * 
     * @param bool $forceFromApi If true, then it gets the access token from the Google API always, even there is still one in the cache.
     * 
     * @return string A non expired access token that can be used to authenticate the API calls.
     */
    public function getToken(bool $forceFromApi = false): string
    {
        $token = $forceFromApi ? null : $this->cache->get(self::CACHE_ACCESS_TOKEN_NAME);
        if (empty($token)) {
            if ($forceFromApi) {
                $this->logger->debug('No access token and forceFromApi, now try the Google API...');
            } else {
                $this->logger->debug('No access token, now try the Google API...');
            }
            $response = $this->fetchAuthToken->fetchAuthToken();
            $token = $response['access_token'];
            $this->logger->debug('Access token from Google API: ' . $token); // Note you should have DISABLED 'debug' log level in production or test!
            // We subtract 30 seconds from the TTL to make sure we have a valid one and can be used for requesting a batch of calls (that can take some time).
            // Currently access tokens are valid for 1 hour. The 'expires_in' is in seconds.
            $this->cache->put(self::CACHE_ACCESS_TOKEN_NAME, $token, $response['expires_in'] - 30);
        }
        $this->logger->debug('Access token: ' . $token);
        return $token;
    }

    /**
     * Returns the project ID.
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
