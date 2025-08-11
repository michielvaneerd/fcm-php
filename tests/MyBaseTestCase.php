<?php

declare(strict_types=1);

namespace Mve\Tests;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Mve\FcmPhp\Models\AccessTokenHandler;
use Mve\FcmPhp\Models\CacheInterface;
use Mve\FcmPhp\Models\Messaging;
use PHPUnit\Framework\TestCase;

class MyBaseTestCase extends TestCase
{
    public const TOPIC_NAME = 'topictest';
    public const NOTIFICATION_TITLE = 'The title';
    public const NOTIFICATION_CONTENT = 'This is the content of the notification';

    private CacheInterface $cache;
    private string $jsonFile;
    protected Logger $logger;
    protected array $tokens;
    protected Messaging $messaging;

    public function echo($s)
    {
        echo "TEST: $s\n";
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = new Logger('debug');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/debug.log', Level::Debug));

        $this->logger->debug('');

        $this->jsonFile = (string)getenv('JSON_FILE');
        $this->assertNotEmpty($this->jsonFile, 'Environment variable JSON_FILE is empty');
        $this->assertFileExists($this->jsonFile, 'File ' . $this->jsonFile . ' does not exist');

        $this->cache = new MyTestCache();
        if (!empty(getenv('ACCESS_TOKEN'))) {
            $this->cache->put(AccessTokenHandler::CACHE_ACCESS_TOKEN_NAME, (string)getenv('ACCESS_TOKEN'));
        }

        $this->tokens = [];
        $envTokens = trim((string)getenv('TOKENS')); // Firebase registration token, this way we can add one valid token.
        if (!empty($envTokens)) {
            $this->tokens = array_map(function ($token) {
                return trim($token);
            }, explode(',', $envTokens));
        }

        $this->messaging = new Messaging($this->cache, $this->jsonFile, $this->logger);
    }

    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
