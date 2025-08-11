<?php

use Mve\FcmPhp\Models\Messaging;
use Mve\FcmPhp\Models\TokenMessage;
use Mve\Tests\MyBaseTestCase;

class MessagingWithEnvTokenTest extends MyBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->assertNotEmpty($this->tokens);
    }

    /**
     * Get information about a registration token.
     */
    public function testGetInfoFromEnvToken()
    {
        $result = $this->messaging->getInfo($this->tokens[0], true);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('application', $result);
    }

    public function testSendAll()
    {
        $this->assertNotEmpty($this->tokens);
        $messages = array_map(function ($token, $index) {
            $id = $index + 1;
            return new TokenMessage($id, $token, self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__ . " #$id", self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__ . " #$id");
        }, $this->tokens, array_keys($this->tokens));
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertEquals(count($this->tokens), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }

    public function testValidateAll()
    {
        $this->assertNotEmpty($this->tokens);
        $messages = array_map(function ($token, $index) {
            $id = $index + 1;
            return new TokenMessage($id, $token, self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__ . " #$id", self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__ . " #$id");
        }, $this->tokens, array_keys($this->tokens));
        $sendResult = $this->messaging->validateAll($messages);
        $this->assertEquals(count($this->tokens), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }

    public function testSubscribeToTopic()
    {
        $result = $this->messaging->subscribeToTopic($this->tokens[0], self::TOPIC_NAME);
        $this->assertTrue($result);
    }

    public function testUnsubscribeFromTopic()
    {
        $result = $this->messaging->unsubscribeFromTopic($this->tokens, self::TOPIC_NAME);
        $this->assertTrue($result);
    }
}
