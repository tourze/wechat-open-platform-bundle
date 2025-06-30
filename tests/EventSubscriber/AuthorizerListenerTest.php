<?php

namespace WechatOpenPlatformBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\EventSubscriber\AuthorizerListener;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

class AuthorizerListenerTest extends TestCase
{
    private $authorizerRepository;
    private $authorizerService;
    private $entityManager;
    private $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizerRepository = $this->createMock(AuthorizerRepository::class);
        $this->authorizerService = $this->createMock(AuthorizerService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new AuthorizerListener(
            $this->authorizerRepository,
            $this->authorizerService,
            $this->entityManager
        );
    }

    public function testServerMessageAuthorizedWithValidMessage(): void
    {
        $account = $this->createMock(Account::class);
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'authorized',
            'AuthorizationCode' => 'auth_code_123'
        ];
        
        $serverMessage->expects($this->exactly(2))
            ->method('getMessage')
            ->willReturn($message);
        
        $serverMessage->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);
        
        $this->authorizerService->expects($this->once())
            ->method('createOrUpdateAuthorizer')
            ->with($account, 'auth_code_123');

        $this->listener->serverMessageAuthorized($serverMessage);
    }

    public function testServerMessageAuthorizedWithDifferentInfoType(): void
    {
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = ['InfoType' => 'other_type'];
        
        $serverMessage->expects($this->once())
            ->method('getMessage')
            ->willReturn($message);
        
        $this->authorizerService->expects($this->never())
            ->method('createOrUpdateAuthorizer');

        $this->listener->serverMessageAuthorized($serverMessage);
    }

    public function testServerMessageUpdateAuthorizedWithValidMessage(): void
    {
        $account = $this->createMock(Account::class);
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'updateauthorized',
            'AuthorizationCode' => 'auth_code_456'
        ];
        
        $serverMessage->expects($this->exactly(2))
            ->method('getMessage')
            ->willReturn($message);
        
        $serverMessage->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);
        
        $this->authorizerService->expects($this->once())
            ->method('createOrUpdateAuthorizer')
            ->with($account, 'auth_code_456');

        $this->listener->serverMessageUpdateAuthorized($serverMessage);
    }

    public function testServerMessageUnauthorizedWithValidMessage(): void
    {
        $account = $this->createMock(Account::class);
        $authorizer = $this->createMock(Authorizer::class);
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'unauthorized',
            'AuthorizerAppid' => 'authorizer_app_id'
        ];
        
        $serverMessage->expects($this->exactly(2))
            ->method('getMessage')
            ->willReturn($message);
        
        $serverMessage->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);
        
        $this->authorizerRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'account' => $account,
                'appId' => 'authorizer_app_id'
            ])
            ->willReturn($authorizer);
        
        $authorizer->expects($this->once())
            ->method('setValid')
            ->with(false);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($authorizer);
        
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->listener->serverMessageUnauthorized($serverMessage);
    }

    public function testServerMessageUnauthorizedWithNonExistentAuthorizer(): void
    {
        $account = $this->createMock(Account::class);
        $serverMessage = $this->createMock(ServerMessage::class);
        
        $message = [
            'InfoType' => 'unauthorized',
            'AuthorizerAppid' => 'non_existent_app_id'
        ];
        
        $serverMessage->expects($this->exactly(2))
            ->method('getMessage')
            ->willReturn($message);
        
        $serverMessage->expects($this->once())
            ->method('getAccount')
            ->willReturn($account);
        
        $this->authorizerRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        
        $this->entityManager->expects($this->never())
            ->method('persist');
        
        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->listener->serverMessageUnauthorized($serverMessage);
    }
}