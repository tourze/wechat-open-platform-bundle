<?php

namespace WechatOpenPlatformBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use WechatOpenPlatformBundle\Controller\AuthCallbackController;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Service\AuthorizerService;
use WeuiBundle\Service\NoticeService;

class AuthCallbackControllerTest extends TestCase
{
    private $noticeService;
    private $entityManager;
    private $logger;
    private $authorizerService;
    private $eventDispatcher;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->noticeService = $this->createMock(NoticeService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->authorizerService = $this->createMock(AuthorizerService::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->controller = new AuthCallbackController(
            $this->noticeService,
            $this->entityManager,
            $this->logger,
            $this->authorizerService,
            $this->eventDispatcher
        );
    }

    public function testInvokeWithAuthCode(): void
    {
        $account = $this->createMock(Account::class);
        $authorizer = $this->createMock(Authorizer::class);
        
        $request = Request::create('/wechat-open-platform/auth-callback/test', 'GET', ['auth_code' => 'test_auth_code']);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('授权回调收到服务端请求', $this->isType('array'));

        $this->authorizerService->expects($this->once())
            ->method('createOrUpdateAuthorizer')
            ->with($account, 'test_auth_code')
            ->willReturn($authorizer);

        $this->noticeService->expects($this->once())
            ->method('weuiSuccess')
            ->with('授权成功')
            ->willReturn(new \Symfony\Component\HttpFoundation\Response());

        $response = $this->controller->__invoke($account, $request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvokeWithAuthCodeNotFound(): void
    {
        $account = $this->createMock(Account::class);
        
        $request = Request::create('/wechat-open-platform/auth-callback/test', 'GET', ['auth_code' => 'test_auth_code']);

        $this->authorizerService->expects($this->once())
            ->method('createOrUpdateAuthorizer')
            ->with($account, 'test_auth_code')
            ->willReturn(null);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('找不到授权信息');

        $this->controller->__invoke($account, $request);
    }

}