<?php

namespace WechatOpenPlatformBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\EventSubscriber\AccountListener;

class AccountListenerTest extends TestCase
{
    private $entityManager;
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->listener = new AccountListener($this->entityManager);
    }

    public function testEnsureLowerCase(): void
    {
        $account = $this->createMock(Account::class);
        
        // 测试大写转小写
        $account->expects($this->once())
            ->method('getAppId')
            ->willReturn('TEST_APP_ID');
        
        $account->expects($this->once())
            ->method('setAppId')
            ->with('test_app_id');

        $this->listener->ensureLowerCase($account);
    }

    public function testEnsureLowerCaseWithMixedCase(): void
    {
        $account = $this->createMock(Account::class);
        
        $account->expects($this->once())
            ->method('getAppId')
            ->willReturn('TeSt_ApP_Id');
        
        $account->expects($this->once())
            ->method('setAppId')
            ->with('test_app_id');

        $this->listener->ensureLowerCase($account);
    }

    public function testSaveComponentVerifyTicketWithValidMessage(): void
    {
        $account = $this->createMock(Account::class);
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'component_verify_ticket',
            'ComponentVerifyTicket' => 'test_ticket_123'
        ];
        
        $serverMessage->expects($this->exactly(2))
            ->method('getMessage')
            ->willReturn($message);
        
        $serverMessage->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);
        
        $account->expects($this->once())
            ->method('setComponentVerifyTicket')
            ->with('test_ticket_123');
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($account);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->listener->saveComponentVerifyTicket($serverMessage);
    }

    public function testSaveComponentVerifyTicketWithDifferentInfoType(): void
    {
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'other_type',
            'ComponentVerifyTicket' => 'test_ticket_123'
        ];
        
        $serverMessage->expects($this->once())
            ->method('getMessage')
            ->willReturn($message);
        
        // 不应该调用其他方法
        $serverMessage->expects($this->never())
            ->method('getAccount');
        
        $this->entityManager->expects($this->never())
            ->method('persist');
        
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->listener->saveComponentVerifyTicket($serverMessage);
    }

    public function testSaveComponentVerifyTicketWithoutInfoType(): void
    {
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'ComponentVerifyTicket' => 'test_ticket_123'
        ];
        
        $serverMessage->expects($this->once())
            ->method('getMessage')
            ->willReturn($message);
        
        // 不应该调用其他方法
        $serverMessage->expects($this->never())
            ->method('getAccount');
        
        $this->entityManager->expects($this->never())
            ->method('persist');
        
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->listener->saveComponentVerifyTicket($serverMessage);
    }
}