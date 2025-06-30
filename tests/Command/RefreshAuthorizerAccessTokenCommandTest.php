<?php

namespace WechatOpenPlatformBundle\Tests\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatOpenPlatformBundle\Command\RefreshAuthorizerAccessTokenCommand;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Request\RefreshAuthorizeAccessTokenRequest;
use WechatOpenPlatformBundle\Service\ApiService;

class RefreshAuthorizerAccessTokenCommandTest extends TestCase
{
    private $authorizerRepository;
    private $apiService;
    private $logger;
    private $entityManager;
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizerRepository = $this->createMock(AuthorizerRepository::class);
        $this->apiService = $this->createMock(ApiService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->command = new RefreshAuthorizerAccessTokenCommand(
            $this->authorizerRepository,
            $this->apiService,
            $this->logger,
            $this->entityManager
        );
    }

    public function testCommandInitialization(): void
    {
        $this->assertEquals(RefreshAuthorizerAccessTokenCommand::NAME, $this->command->getName());
        $this->assertEquals('刷新授权方AccessToken', $this->command->getDescription());
    }

    public function testExecuteWithNoAuthorizers(): void
    {
        $this->authorizerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithAuthorizerWithoutRefreshToken(): void
    {
        $authorizer = $this->createMock(Authorizer::class);
        $authorizer->expects($this->once())
            ->method('getAuthorizerRefreshToken')
            ->willReturn(null);

        $this->authorizerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$authorizer]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteRefreshesTokenWhenNeeded(): void
    {
        $account = $this->createMock(Account::class);
        $authorizer = $this->createMock(Authorizer::class);
        
        $authorizer->method('getAuthorizerRefreshToken')->willReturn('refresh_token');
        $authorizer->method('getAccessTokenExpireTime')
            ->willReturn(CarbonImmutable::now()->addMinutes(5)); // 剩余5分钟，需要刷新
        $authorizer->method('getAccount')->willReturn($account);
        $authorizer->method('getAppId')->willReturn('app_id');

        $this->authorizerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$authorizer]);

        $this->apiService->expects($this->once())
            ->method('request')
            ->with($this->isInstanceOf(RefreshAuthorizeAccessTokenRequest::class))
            ->willReturn([
                'authorizer_access_token' => 'new_access_token',
                'expires_in' => 7200,
                'authorizer_refresh_token' => 'new_refresh_token'
            ]);

        $authorizer->expects($this->once())
            ->method('setAuthorizerAccessToken')
            ->with('new_access_token');
        $authorizer->expects($this->once())
            ->method('setAccessTokenExpireTime');
        $authorizer->expects($this->once())
            ->method('setAuthorizerRefreshToken')
            ->with('new_refresh_token');

        $this->entityManager->expects($this->once())->method('persist')->with($authorizer);
        $this->entityManager->expects($this->once())->method('flush');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testExecuteHandlesApiException(): void
    {
        $account = $this->createMock(Account::class);
        $authorizer = $this->createMock(Authorizer::class);
        
        $authorizer->method('getAuthorizerRefreshToken')->willReturn('refresh_token');
        $authorizer->method('getAccessTokenExpireTime')
            ->willReturn(CarbonImmutable::now()->addMinutes(5));
        $authorizer->method('getAccount')->willReturn($account);
        $authorizer->method('getAppId')->willReturn('app_id');

        $this->authorizerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$authorizer]);

        $this->apiService->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('API Error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('更新授权者token时发生错误', $this->isType('array'));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
    }
}