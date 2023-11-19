<?php

declare(strict_types=1);

use Mve\FcmPhp\Models\FcmError;
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

    public function setUp(): void
    {
        parent::setUp();
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

    public function testSendAll()
    {
        $messages = [
            new TokenMessage(1, self::CORRECTLY_FORMATTED_TOKEN, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE),
            new TokenMessage(2, self::INVALID_TOKEN, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE),
        ];
        $sendResult = $this->messaging->sendAll($messages);
        $unregistered = $sendResult->getUnregistered();
        $errors = $sendResult->getErrors();
        $this->assertCount(1, $unregistered);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(FcmError::class, $unregistered[1]);
        $this->assertInstanceOf(FcmError::class, $errors[2]);
    }

    public function testSendToTopic()
    {
        $message = new TopicMessage(1, self::TOPIC_NAME, self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__, self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__);
        $result = $this->messaging->sendToTopic($message);
        $this->assertTrue($result);
    }

    public function testValidateAll()
    {
        $messages = [
            new TokenMessage(1, self::CORRECTLY_FORMATTED_TOKEN, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE),
            new TokenMessage(2, self::INVALID_TOKEN, self::NOTIFICATION_CONTENT, self::NOTIFICATION_TITLE),
        ];
        $sendResult = $this->messaging->validateAll($messages);
        $unregistered = $sendResult->getUnregistered();
        $errors = $sendResult->getErrors();
        $this->assertCount(1, $unregistered);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(FcmError::class, $unregistered[1]);
        $this->assertInstanceOf(FcmError::class, $errors[2]);
    }
}
