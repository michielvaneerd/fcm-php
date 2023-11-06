<?php

namespace Mve\FcmPhp\Models;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\FetchAuthTokenInterface;

class AccessTokenHandler
{
    private CacheInterface $cache;
    private FetchAuthTokenInterface $fetchAuthToken;
    private string $projectId;

    private const CACHE_ACCESS_TOKEN_NAME = 'mve_fcm_php_token';

    const SCOPE_FIREBASE_MESSAGING = 'https://www.googleapis.com/auth/firebase.messaging';

    function __construct(CacheInterface $cache, string $jsonFile)
    {
        $this->cache = $cache;
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $jsonFile);
        $json = json_decode(file_get_contents($jsonFile));
        $this->projectId = $json->project_id;
        $this->fetchAuthToken = ApplicationDefaultCredentials::getCredentials(self::SCOPE_FIREBASE_MESSAGING);
    }

    public function getToken(?bool $forceFromApi = false): string
    {
        $token = $forceFromApi ? null : $this->cache->get(self::CACHE_ACCESS_TOKEN_NAME);
        if (empty($token)) {
            $response = $this->fetchAuthToken->fetchAuthToken();
            $token = $response['access_token'];
            // We subtract 30 seconds from the TTL to make sure we have a valid one and can be used for requesting a batch of calls (that can take some time).
            // Currently access tokens are valid for 1 hour. The 'expires_in' is in seconds.
            $this->cache->put(self::CACHE_ACCESS_TOKEN_NAME, $token, $response['expires_in'] - 30);
        }
        return $token;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }
}
