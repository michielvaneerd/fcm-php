<?php declare(strict_types=1);

use Mve\Tests\MyBaseTestCase;

class AccessTokenHandlerTest extends MyBaseTestCase
{
    // public function testGetTokenWithForceFromAPI(): void
    // {
    //     $this->assertNotEmpty($this->accessTokenHandler->getProjectId());
    //     $accessToken = $this->accessTokenHandler->getToken(true);
    //     $this->assertNotEmpty($accessToken);
    // }

    /**
     * Test getting access token from cache and if this does not exist, get it from the Google API and write it to cache.
     */
    public function testGetToken(): void
    {
        $this->assertNotEmpty($this->accessTokenHandler->getProjectId());
        $accessToken = $this->accessTokenHandler->getToken();
        $this->assertNotEmpty($accessToken);
    }
}