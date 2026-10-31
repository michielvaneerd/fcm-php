<?php

declare(strict_types=1);

use Mve\FcmPhp\Models\FcmError;
use Mve\FcmPhp\Models\FcmException;
use Mve\FcmPhp\Models\Message;
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
        $msg1 = new Message(1);
        $msg1->setToken(self::CORRECTLY_FORMATTED_TOKEN);
        $msg1->setNotification(title: self::NOTIFICATION_TITLE, body: self::NOTIFICATION_CONTENT);
        $msg2 = new Message(2);
        $msg2->setToken(self::INVALID_TOKEN);
        $msg2->setNotification(title: self::NOTIFICATION_TITLE, body: self::NOTIFICATION_CONTENT);
        $messages = [
            $msg1,
            $msg2
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
        $msg = new Message(1);
        $msg->setTopic(self::TOPIC_NAME);
        $msg->setNotification(title: self::NOTIFICATION_TITLE, body: self::NOTIFICATION_CONTENT);
        $result = $this->messaging->sendToTopic($msg);
        $this->assertTrue($result);
    }

    public function testValidateAll()
    {
        $msg1 = new Message(1);
        $msg1->setToken(self::CORRECTLY_FORMATTED_TOKEN);
        $msg1->setNotification(title: self::NOTIFICATION_TITLE, body: self::NOTIFICATION_CONTENT);
        $msg2 = new Message(2);
        $msg2->setToken(self::INVALID_TOKEN);
        $msg2->setNotification(title: self::NOTIFICATION_TITLE, body: self::NOTIFICATION_CONTENT);
        $messages = [
            $msg1,
            $msg2
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
