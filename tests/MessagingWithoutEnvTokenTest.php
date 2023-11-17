<?php

declare(strict_types=1);

use Mve\FcmPhp\Models\FcmException;
use Mve\FcmPhp\Models\Message;
use Mve\FcmPhp\Models\Messaging;
use Mve\FcmPhp\Models\TokenMessage;
use Mve\FcmPhp\Models\TopicMessage;
use Mve\Tests\MyBaseTestCase;

class MessagingWithoutEnvTokenTest extends MyBaseTestCase
{
    private const CORRECTLY_FORMATTED_TOKEN = 'fKXG4UWOSVKh1XATm_14ZW:APA91bE59fuCLrQoq6HK1CJqJBQ29v9jY3QjhZ1aJXhD54F54SOpeMnYYudlCrMwWw3Plw-X9585-PYcYsjyFzuMlDmeBpYOOcUajTKQFoAmdrcSISLbF-jk7NmZXcDkCm9iVoFGJY_J';
    private const INVALID_TOKEN = 'abc';

    private Messaging $messaging;

    public function setUp(): void
    {
        parent::setUp();
        $this->messaging = new Messaging($this->accessTokenHandler, $this->logger);
    }

    public function testGetInfoFromUnregisteredToken()
    {
        $this->expectException(FcmException::class);
        $this->expectExceptionCode(404);
        $this->messaging->getInfo(self::CORRECTLY_FORMATTED_TOKEN, true);
    }

    public function testGetInfoFromInvalidToken()
    {
        $this->expectException(FcmException::class);
        $this->expectExceptionCode(400);
        $this->messaging->getInfo(self::INVALID_TOKEN, true);
    }

    public function testSendUnregisteredToken()
    {
        $token = self::CORRECTLY_FORMATTED_TOKEN;
        $messages = [
            new TokenMessage(1, $token, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE)
        ];
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertCount(1, $sendResult->invalidIds);
        $this->assertEquals(1, $sendResult->invalidIds[0]);
    }

    public function testSendInvalidToken()
    {
        $token = self::INVALID_TOKEN;
        $messages = [
            new Message(1, $token, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE)
        ];
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertCount(1, $sendResult->errorIds);
        $this->assertInstanceOf(FcmException::class, $sendResult->errorIds[1]);
    }

    public function testSendToTopic()
    {
        $message = new TopicMessage(1, self::TOPIC_NAME, self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__, self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__);
        $result = $this->messaging->sendToTopic($message);
        $this->assertTrue($result);
    }

    public function testUnsubscribeFromTopic()
    {
        $result = $this->messaging->unsubscribeFromTopic($this->tokens, self::TOPIC_NAME);
        $this->assertTrue($result);
    }
}
