<?php

use Mve\FcmPhp\Models\Message;
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
            $msg = new Message($id);
            $msg->setFID($fid);
            $msg->setData([
                'type' => 'incoming_call',
                'callId' => '0611111111',
                'callerName' => 'Pete Sampras'
            ]);
            return $msg;
        }, $this->fids, array_keys($this->fids));
        $sendResult = $this->messaging->sendAll($messages);
        $this->assertEquals(count($this->fids), count($sendResult->getSent()) + count($sendResult->getUnregistered()) + count($sendResult->getErrors()));
    }
}
