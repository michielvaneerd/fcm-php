<?php

declare(strict_types=1);

use Mve\Tests\MyBaseTestCase;

class AccessTokenHandlerTest extends MyBaseTestCase
{
    /**
     * Test getting access token from Google API.
     */
    public function testGetTokenWithForceFromAPI(): void
    {
        $this->assertNotEmpty($this->messaging->getAccessTokenHandler()->getProjectId());
        $accessToken = $this->messaging->getAccessTokenHandler()->getToken(true);
        $this->assertNotEmpty($accessToken);
    }

    /**
     * Test getting access token from cache.
     */
    public function testGetToken(): void
    {
        $this->assertNotEmpty($this->messaging->getAccessTokenHandler()->getProjectId());
        $accessToken = $this->messaging->getAccessTokenHandler()->getToken();
        $this->assertNotEmpty($accessToken);
    }
}
