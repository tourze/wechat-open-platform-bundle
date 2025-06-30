<?php

namespace WechatOpenPlatformBundle\Tests\Service;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use WechatOfficialAccountBundle\Entity\Account as OfficialAccount;
use WechatOfficialAccountBundle\Repository\AccountRepository as OfficialAccountRepository;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Request\QueryAuthInfoRequest;
use WechatOpenPlatformBundle\Service\ApiService;
use WechatOpenPlatformBundle\Service\AuthorizerService;

class AuthorizerServiceTest extends TestCase
{
    private $apiService;
    private $authorizerRepository;
    private $logger;
    private $doctrineService;
    private $officialAccountRepository;
    private $entityManager;
    private $authorizerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiService = $this->createMock(ApiService::class);
        $this->authorizerRepository = $this->createMock(AuthorizerRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->doctrineService = $this->createMock(AsyncInsertService::class);
        $this->officialAccountRepository = $this->createMock(OfficialAccountRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->authorizerService = new AuthorizerService(
            $this->apiService,
            $this->authorizerRepository,
            $this->logger,
            $this->doctrineService,
            $this->officialAccountRepository,
            $this->entityManager
        );
    }

    public function testServiceCreation(): void
    {
        $this->assertInstanceOf(AuthorizerService::class, $this->authorizerService);
    }
}