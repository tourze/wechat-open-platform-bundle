<?php

namespace WechatOpenPlatformBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WechatOpenPlatformBundle\Controller\OAuthBaseInfoController;
use WechatOpenPlatformBundle\Entity\OfficialAccount;
use WechatOpenPlatformBundle\Repository\AccountRepository;
use WechatOpenPlatformBundle\Repository\UserRepository;
use WeuiBundle\Service\NoticeService;

class OAuthBaseInfoControllerTest extends TestCase
{
    private $noticeService;
    private $entityManager;
    private $userRepository;
    private $eventDispatcher;
    private $componentAccountRepository;
    private $logger;
    private $encryptor;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->noticeService = $this->createMock(NoticeService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->componentAccountRepository = $this->createMock(AccountRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->encryptor = $this->createMock(Encryptor::class);

        $this->controller = new OAuthBaseInfoController(
            $this->noticeService,
            $this->entityManager,
            $this->userRepository,
            $this->eventDispatcher,
            $this->componentAccountRepository,
            $this->logger,
            $this->encryptor
        );
    }

    public function testInvokeWithCode(): void
    {
        $account = $this->createMock(OfficialAccount::class);
        $account->method('getComponentAppId')->willReturn('component_app_id');
        
        $componentAccount = $this->createMock(\WechatOpenPlatformBundle\Entity\Account::class);
        
        $request = Request::create('/wechat-open-platform/base-user/test', 'GET', ['code' => 'test_code']);

        $this->componentAccountRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['appId' => 'component_app_id'])
            ->willReturn($componentAccount);

        $this->logger->expects($this->once())
            ->method('info');

        $this->entityManager->expects($this->atLeastOnce())
            ->method('persist');
        $this->entityManager->expects($this->atLeastOnce())
            ->method('flush');

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch');

        $this->noticeService->expects($this->once())
            ->method('weuiSuccess')
            ->willReturn(new Response('Success'));

        $response = $this->controller->__invoke($account, $request);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInvokeWithoutCode(): void
    {
        $account = $this->createMock(OfficialAccount::class);
        $account->method('getComponentAppId')->willReturn('component_app_id');
        $account->method('getAppId')->willReturn('app_id');
        
        $componentAccount = $this->createMock(\WechatOpenPlatformBundle\Entity\Account::class);
        
        $request = Request::create('/wechat-open-platform/base-user/test', 'GET');

        $this->componentAccountRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($componentAccount);

        $response = $this->controller->__invoke($account, $request);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testInvokeWithUnsupportedAccount(): void
    {
        $account = $this->createMock(OfficialAccount::class);
        $account->method('getComponentAppId')->willReturn(null);
        
        $request = Request::create('/wechat-open-platform/base-user/test', 'GET');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('该公众号不支持开放平台授权');

        $this->controller->__invoke($account, $request);
    }
}