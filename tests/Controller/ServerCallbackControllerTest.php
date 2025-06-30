<?php

namespace WechatOpenPlatformBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WechatOpenPlatformBundle\Controller\ServerCallbackController;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

class ServerCallbackControllerTest extends TestCase
{
    private $authorizerService;
    private $entityManager;
    private $logger;
    private $eventDispatcher;
    private $authorizerRepository;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizerService = $this->createMock(AuthorizerService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->authorizerRepository = $this->createMock(AuthorizerRepository::class);

        $this->controller = new ServerCallbackController(
            $this->authorizerService,
            $this->entityManager,
            $this->logger,
            $this->authorizerRepository,
            $this->eventDispatcher
        );
    }

    public function testControllerCreation(): void
    {
        $this->assertInstanceOf(ServerCallbackController::class, $this->controller);
    }
}