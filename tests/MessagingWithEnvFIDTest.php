<?php

use Mve\FcmPhp\Models\Message;
use Mve\Tests\MyBaseTestCase;

class MessagingWithEnvFIDTest extends MyBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->assertNotEmpty($this->fids);
    }

    /**
     * Get information about a registration token.
     */
    // public function testGetInfoFromEnvToken()
    // {
    //     $result = $this->messaging->getInfo($this->fids[0], true);
    //     $this->assertIsArray($result);
    //     $this->assertArrayHasKey('application', $result);
    // }

    public function testSendAll()
    {
        $this->assertNotEmpty($this->fids);
        $messages = array_map(function ($fid, $index) {
            $id = $index + 1;
            return new Message(
                $id,
                fid: $fid,
                title: self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__ . " #$id",
                body: self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__ . " #$id"
            );
        }, $this->fids, array_keys($this->fids));
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertEquals(count($this->fids), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }

    public function testValidateAll()
    {
        $this->assertNotEmpty($this->fids);
        $messages = array_map(function ($fid, $index) {
            $id = $index + 1;
            return new Message(
                $id,
                fid: $fid,
                title: self::NOTIFICATION_TITLE . ' - ' . __FUNCTION__ . " #$id",
                body: self::NOTIFICATION_CONTENT . ' - ' . __FUNCTION__ . " #$id"
            );
        }, $this->fids, array_keys($this->fids));
        $sendResult = $this->messaging->validateAll($messages);
        $this->assertEquals(count($this->fids), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }

    public function testSubscribeToTopic()
    {
        $result = $this->messaging->subscribeToTopic($this->fids[0], self::TOPIC_NAME);
        $this->assertTrue($result);
    }

    public function testUnsubscribeFromTopic()
    {
        $result = $this->messaging->unsubscribeFromTopic($this->fids[0], self::TOPIC_NAME);
        $this->assertTrue($result);
    }
}
