<?php

use Mve\FcmPhp\Models\Message;
use Mve\FcmPhp\Models\MessageAndroidConfig;
use Mve\Tests\MyBaseTestCase;

class MessagingDataWithEnvFIDTest extends MyBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->assertNotEmpty($this->fids);
    }

    public function testSendAll()
    {
        $this->assertNotEmpty($this->fids);
        $messages = array_map(function ($fid, $index) {
            $id = $index + 1;
            $msg = new Message($id, fid: $fid, data: [
                'type' => 'incoming_call',
                'callId' => '0611111111',
                'callerName' => 'Pete Sampras'
            ], androidConfig: new MessageAndroidConfig(priority: MessageAndroidConfig::PRIORITY_HIGH));
            return $msg;
        }, $this->fids, array_keys($this->fids));
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertEquals(count($this->fids), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }
}
