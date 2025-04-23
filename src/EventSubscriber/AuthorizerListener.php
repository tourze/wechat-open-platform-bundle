<?php

namespace WechatOpenPlatformBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;
use WechatOpenPlatformBundle\Service\AuthorizerService;

#[AsEntityListener(event: Events::postPersist, method: 'serverMessageAuthorized', entity: ServerMessage::class)]
#[AsEntityListener(event: Events::postPersist, method: 'serverMessageUpdateAuthorized', entity: ServerMessage::class)]
#[AsEntityListener(event: Events::postPersist, method: 'serverMessageUnauthorized', entity: ServerMessage::class)]
class AuthorizerListener
{
    public function __construct(
        private readonly AuthorizerRepository $authorizerRepository,
        private readonly AuthorizerService $authorizerService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 授权成功通知
     * <xml>
     * <AppId>第三方平台appid</AppId>
     * <CreateTime>1413192760</CreateTime>
     * <InfoType>authorized</InfoType>
     * <AuthorizerAppid>公众号appid</AuthorizerAppid>
     * <AuthorizationCode>授权码</AuthorizationCode>
     * <AuthorizationCodeExpiredTime>过期时间</AuthorizationCodeExpiredTime>
     * <PreAuthCode>预授权码</PreAuthCode>
     * <xml>
     */
    public function serverMessageAuthorized(ServerMessage $message): void
    {
        if ($message->getMessage()['InfoType'] ?? '' !== 'authorized') {
            return;
        }
        $this->authorizerService->createOrUpdateAuthorizer($message->getAccount(), $message->getAccount()['AuthorizationCode']);
    }

    /**
     * 授权更新通知
     * <xml>
     * <AppId>第三方平台appid</AppId>
     * <CreateTime>1413192760</CreateTime>
     * <InfoType>updateauthorized</InfoType>
     * <AuthorizerAppid>公众号appid</AuthorizerAppid>
     * <AuthorizationCode>授权码</AuthorizationCode>
     * <AuthorizationCodeExpiredTime>过期时间</AuthorizationCodeExpiredTime>
     * <PreAuthCode>预授权码</PreAuthCode>
     * <xml>
     */
    public function serverMessageUpdateAuthorized(ServerMessage $message): void
    {
        if ($message->getMessage()['InfoType'] ?? '' !== 'updateauthorized') {
            return;
        }
        $this->authorizerService->createOrUpdateAuthorizer($message->getAccount(), $message->getAccount()['AuthorizationCode']);
    }

    /**
     * 取消授权通知
     * <xml>
     * <AppId>第三方平台appid</AppId>
     * <CreateTime>1413192760</CreateTime>
     * <InfoType>unauthorized</InfoType>
     * <AuthorizerAppid>公众号appid</AuthorizerAppid>
     * </xml>
     */
    public function serverMessageUnauthorized(ServerMessage $message): void
    {
        if ($message->getMessage()['InfoType'] ?? '' !== 'unauthorized') {
            return;
        }

        $authorizer = $this->authorizerRepository->findOneBy([
            'account' => $message->getAccount(),
            'appId' => $message->getMessage()['AuthorizerAppid'],
        ]);
        if (!$authorizer) {
            return;
        }
        $authorizer->setValid(false);
        $this->entityManager->persist($authorizer);
        $this->entityManager->flush();
    }
}
