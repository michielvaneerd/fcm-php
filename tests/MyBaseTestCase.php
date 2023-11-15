<?php

declare(strict_types=1);

namespace Mve\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Mve\FcmPhp\Models\AccessTokenHandler;
use Mve\FcmPhp\Models\CacheInterface;

class MyBaseTestCase extends MockeryTestCase
{
    private CacheInterface $cache;
    private string $jsonFile;
    protected Logger $logger;
    protected AccessTokenHandler $accessTokenHandler;
    protected array $tokens;

    public function echo($s)
    {
        echo "TEST: $s\n";
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->jsonFile = (string)getenv('JSON_FILE');

        $this->cache = new MyTestCache();
        if (!empty(getenv('ACCESS_TOKEN'))) {
            $this->cache->put(AccessTokenHandler::CACHE_ACCESS_TOKEN_NAME, (string)getenv('ACCESS_TOKEN'));
        }

        $this->tokens = [];
        $envTokens = trim((string)getenv('TOKENS')); // Firebase registration token, this way we can add one valid token.
        if (!empty($envTokens)) {
            $this->tokens = array_map(function($token) {
                return trim($token);
            }, explode(',', $envTokens));
        }

        $this->logger = new Logger('debug');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/debug.log', Level::Debug));

        // $this->cache = \Mockery::mock(CacheInterface::class);
        // $this->cache->allows([
        //     'get' => $this->accessToken,
        //     'put' => null
        // ]);

        $this->accessTokenHandler = new AccessTokenHandler($this->cache, $this->jsonFile, $this->logger);
    }

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
