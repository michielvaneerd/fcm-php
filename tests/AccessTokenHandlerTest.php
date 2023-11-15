<?php declare(strict_types=1);

use Mve\Tests\MyBaseTestCase;

class AccessTokenHandlerTest extends MyBaseTestCase
{
    public function testGetToken(?bool $forceFromApi = false): void
    {
        $this->assertNotEmpty($this->accessTokenHandler->getProjectId());
        $this->echo("Project ID = " . $this->accessTokenHandler->getProjectId() . "\n");
        $accessToken = $this->accessTokenHandler->getToken();
        $this->echo("Access token = $accessToken\n");
        $this->assertNotEmpty($accessToken);
    }
}