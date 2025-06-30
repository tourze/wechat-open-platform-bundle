<?php

namespace WechatOpenPlatformBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '将授权信息同步一份到 WechatOfficialAccountBundle')]
class SyncWechatOfficialAccountCommand extends Command
{
    public const NAME = 'wechat-open-platform:sync-wechat-official-account';

    public function __construct(
        private readonly AuthorizerRepository $authorizerRepository,
        private readonly AuthorizerService $authorizerService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->authorizerRepository->findAll() as $authorizer) {
            $account = $this->authorizerService->transformToOfficialAccount($authorizer);
            $output->writeln("保存/更新{$account->getAppId()}");
        }

        return Command::SUCCESS;
    }
}
