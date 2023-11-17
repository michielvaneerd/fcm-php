<?php declare(strict_types=1);

use Mve\Tests\MyBaseTestCase;

class AccessTokenHandlerTest extends MyBaseTestCase
{
    /**
     * Test getting access token from Google API.
     */
    public function testGetTokenWithForceFromAPI(): void
    {
        $this->assertNotEmpty($this->accessTokenHandler->getProjectId());
        $accessToken = $this->accessTokenHandler->getToken(true);
        $this->assertNotEmpty($accessToken);
    }

    /**
     * Test getting access token from cache.
     */
    public function testGetToken(): void
    {
        $this->assertNotEmpty($this->accessTokenHandler->getProjectId());
        $accessToken = $this->accessTokenHandler->getToken();
        $this->assertNotEmpty($accessToken);
    }
}