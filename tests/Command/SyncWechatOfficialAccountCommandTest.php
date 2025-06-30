<?php

namespace WechatOpenPlatformBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use WechatOpenPlatformBundle\Command\SyncWechatOfficialAccountCommand;
use WechatOpenPlatformBundle\Entity\Authorizer;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

class SyncWechatOfficialAccountCommandTest extends TestCase
{
    private $authorizerRepository;
    private $authorizerService;
    private $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizerRepository = $this->createMock(AuthorizerRepository::class);
        $this->authorizerService = $this->createMock(AuthorizerService::class);

        $this->command = new SyncWechatOfficialAccountCommand(
            $this->authorizerRepository,
            $this->authorizerService
        );
    }

    public function testCommandInitialization(): void
    {
        $this->assertEquals(SyncWechatOfficialAccountCommand::NAME, $this->command->getName());
        $this->assertEquals('将授权信息同步一份到 WechatOfficialAccountBundle', $this->command->getDescription());
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
        $this->assertEquals('', trim($commandTester->getDisplay()));
    }

    public function testExecuteSyncsAuthorizers(): void
    {
        $authorizer1 = $this->createMock(Authorizer::class);
        $authorizer2 = $this->createMock(Authorizer::class);

        $account1 = $this->createMock(\WechatOfficialAccountBundle\Entity\Account::class);
        $account1->method('getAppId')->willReturn('app_id_1');
        
        $account2 = $this->createMock(\WechatOfficialAccountBundle\Entity\Account::class);
        $account2->method('getAppId')->willReturn('app_id_2');

        $this->authorizerRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$authorizer1, $authorizer2]);

        $this->authorizerService->expects($this->exactly(2))
            ->method('transformToOfficialAccount')
            ->willReturnMap([
                [$authorizer1, $account1],
                [$authorizer2, $account2]
            ]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('保存/更新app_id_1', $output);
        $this->assertStringContainsString('保存/更新app_id_2', $output);
    }
}