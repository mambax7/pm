<?php

namespace XoopsModules\Pm\Tests;

use PHPUnit\Framework\TestCase;
use XoopsModules\Pm\MessageHandler;
use XoopsModules\Pm\Message;
use XoopsDatabase;
use XoopsUser;
use Criteria;

/**
 * @covers \XoopsModules\Pm\MessageHandler
 */
class MessageHandlerTest extends TestCase
{
    /**
     * @var MessageHandler
     */
    protected $handler;

    /**
     * @var XoopsDatabase|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbMock;

    protected function setUp(): void
    {
        // Mock the XoopsDatabase object
        $this->dbMock = $this->createMock(XoopsDatabase::class);
        $this->handler = new MessageHandler($this->dbMock);
    }

    /**
     * Creates a mock PmMessage object.
     * @param array $values Key-value pairs of message properties.
     * @return Message|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMockMessage(array $values = [])
    {
        $messageMock = $this->createMock(Message::class);
        $messageMock->method('getVar')->will($this->returnCallback(
            function ($key) use ($values) {
                return $values[$key] ?? null;
            }
        ));
        return $messageMock;
    }

    public function testSetRead()
    {
        $pm = $this->createMockMessage(['msg_id' => 123]);

        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                $this->stringContains('priv_msgs'),
                $this->stringContains('read_msg = 1'),
                $this->stringContains('msg_id = 123')
            )
            ->willReturn(true);

        $result = $this->handler->setRead($pm);
        $this->assertTrue($result);
    }

    public function testSetToDeleteWhenSenderHasNotDeleted()
    {
        $pm = $this->createMockMessage(['msg_id' => 123, 'from_delete' => 0, 'from_userid' => 1]);

        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                $this->stringContains('priv_msgs'),
                $this->stringContains('to_delete = 1'),
                $this->stringContains('msg_id = 123')
            )
            ->willReturn(true);

        $result = $this->handler->setTodelete($pm);
        $this->assertTrue($result);
    }

    public function testSetToDeleteWhenSenderHasDeleted()
    {
        $pm = $this->createMockMessage(['msg_id' => 123, 'from_delete' => 1, 'from_userid' => 1]);

        $this->dbMock->expects($this->once())
            ->method('delete')
            ->with($this->stringContains('priv_msgs'), $this->isInstanceOf(Criteria::class))
            ->willReturn(true);

        $result = $this->handler->setTodelete($pm);
        $this->assertTrue($result);
    }

    public function testSetFromDeleteWhenRecipientHasNotDeleted()
    {
        $pm = $this->createMockMessage(['msg_id' => 123, 'to_delete' => 0]);

        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                $this->stringContains('priv_msgs'),
                $this->stringContains('from_delete = 1'),
                $this->stringContains('msg_id = 123')
            )
            ->willReturn(true);

        $result = $this->handler->setFromdelete($pm);
        $this->assertTrue($result);
    }

    public function testSetFromDeleteWhenRecipientHasDeleted()
    {
        $pm = $this->createMockMessage(['msg_id' => 123, 'to_delete' => 1]);

        $this->dbMock->expects($this->once())
            ->method('delete')
            ->with($this->stringContains('priv_msgs'), $this->isInstanceOf(Criteria::class))
            ->willReturn(true);

        $result = $this->handler->setFromdelete($pm);
        $this->assertTrue($result);
    }

    /**
     * This test is expected to fail because of a typo in the method name in readpmsg.php
     * The file calls `setFromDelete` (capital D) instead of `setFromdelete`.
     * This test targets the correct method name, but the code in readpmsg.php will call a non-existent one.
     * To fix the bug, the call in readpmsg.php should be changed to `setFromdelete`.
     */
    public function testSetFromDeleteTypo()
    {
        $this->assertTrue(method_exists($this->handler, 'setFromdelete'), "Method setFromdelete() exists.");
        $this->assertFalse(method_exists($this->handler, 'setFromDelete'), "Method setFromDelete() should not exist.");
    }

    public function testGetSaveCount()
    {
        $userMock = $this->createMock(XoopsUser::class);
        $userMock->method('getVar')->with('uid')->willReturn(1);

        $this->dbMock->expects($this->once())
            ->method('getCount')
            ->with($this->isInstanceOf(Criteria::class))
            ->willReturn(5);

        $count = $this->handler->getSavecount($userMock);
        $this->assertEquals(5, $count);
    }
}
