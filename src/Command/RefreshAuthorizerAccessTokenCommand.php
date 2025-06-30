<?php

namespace WechatOpenPlatformBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Request\RefreshAuthorizeAccessTokenRequest;
use WechatOpenPlatformBundle\Service\ApiService;

#[AsCronTask(expression: '*/5 * * * *')]
#[AsCommand(name: self::NAME, description: '刷新授权方AccessToken')]
class RefreshAuthorizerAccessTokenCommand extends Command
{
    public const NAME = 'wechat-open-platform:refresh-authorizer-access-token';
    public function __construct(
        private readonly AuthorizerRepository $authorizerRepository,
        private readonly ApiService $apiService,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->authorizerRepository->findAll() as $authorizer) {
            if ($authorizer->getAuthorizerRefreshToken() === null) {
                continue;
            }

            // 如果剩余时间不够10分钟了，那就开始更新
            if (CarbonImmutable::now()->diffInMinutes($authorizer->getAccessTokenExpireTime()) < 10) {
                try {
                    $request = new RefreshAuthorizeAccessTokenRequest();
                    $request->setAccount($authorizer->getAccount());
                    $request->setAuthorizerAppId($authorizer->getAppId());
                    $request->setAuthorizerRefreshToken($authorizer->getAuthorizerRefreshToken());
                    $authCodeRes = $this->apiService->request($request);
                } catch (\Throwable $exception) {
                    $this->logger->error('更新授权者token时发生错误', [
                        'authorizer' => $authorizer,
                        'exception' => $exception,
                    ]);
                    continue;
                }

                if (!isset($authCodeRes['authorizer_access_token'])) {
                    continue;
                }

                $authorizer->setAuthorizerAccessToken($authCodeRes['authorizer_access_token']);
                $authorizer->setAccessTokenExpireTime(CarbonImmutable::now()->addSeconds($authCodeRes['expires_in']));
                $authorizer->setAuthorizerRefreshToken($authCodeRes['authorizer_refresh_token'] ?? '');
                $this->entityManager->persist($authorizer);
                $this->entityManager->flush();
            }
        }

        return Command::SUCCESS;
    }
}
