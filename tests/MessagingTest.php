<?php

declare(strict_types=1);

use Mve\FcmPhp\Models\FcmException;
use Mve\FcmPhp\Models\Message;
use Mve\FcmPhp\Models\Messaging;
use Mve\Tests\MyBaseTestCase;

class MessagingTest extends MyBaseTestCase
{
    private const CORRECTLY_FORMATTED_TOKEN = 'fKXG4UWOSVKh1XATm_14ZW:APA91bE59fuCLrQoq6HK1CJqJBQ29v9jY3QjhZ1aJXhD54F54SOpeMnYYudlCrMwWw3Plw-X9585-PYcYsjyFzuMlDmeBpYOOcUajTKQFoAmdrcSISLbF-jk7NmZXcDkCm9iVoFGJY_J';

    private Messaging $messaging;

    public function setUp(): void
    {
        parent::setUp();
        $this->messaging = new Messaging($this->accessTokenHandler, $this->logger);
    }

    public function testEnvTokens()
    {
        $this->assertNotEmpty($this->tokens);
        $messages = array_map(function ($token, $index) {
            $id = $index + 1;
            return new Message($id, $token, 'This is the body', 'The title');
        }, $this->tokens, array_keys($this->tokens));
        // Blijkbaar vuren asserts ook exceptions af en als die afvang, dan zie ik GEEN output + ik zie dan de hele exception var_dump().
        // Dus ik moet GEEN exception handling doen blijkbaar.
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertEquals(count($this->tokens), count($sendResult->sentIds) + count($sendResult->invalidIds) + count($sendResult->errorIds));
    }

    public function testSendCorrectlyFormattedButUnregisteredToken()
    {
        $token = self::CORRECTLY_FORMATTED_TOKEN;
        $messages = [
            new Message(1, $token, 'This is the body', 'The title')
        ];
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertCount(1, $sendResult->invalidIds);
        $this->assertEquals(1, $sendResult->invalidIds[0]);
    }

    public function testSendInvalidFormattedToken()
    {
        $token = 'abc';
        $messages = [
            new Message(1, $token, 'This is the body', 'The title')
        ];
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertCount(1, $sendResult->errorIds);
        $this->assertInstanceOf(FcmException::class, $sendResult->errorIds[1]);
    }
}
