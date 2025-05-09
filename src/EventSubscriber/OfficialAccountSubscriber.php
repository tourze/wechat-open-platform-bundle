<?php

namespace WechatOpenPlatformBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use WechatOfficialAccountBundle\Entity\ServerMessage;
use WechatOfficialAccountBundle\Event\WechatOfficialAccountServerMessageRequestEvent;
use WechatOpenPlatformBundle\Event\WechatOpenPlatformServerMessageResponseEvent;
use WechatOpenPlatformBundle\Service\AuthorizerService;

class OfficialAccountSubscriber
{
    public function __construct(
        private readonly AuthorizerService $authorizerService,
        private readonly UserService $userService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * 开放平台在收到消息之后，我们转发一次给原来的微信公众号模块处理
     */
    #[AsEventListener]
    public function onWechatOpenPlatformServerMessageResponse(WechatOpenPlatformServerMessageResponseEvent $event): void
    {
        $message = $event->getMessage()->getMessage();
        if (!isset($message['FromUserName'])) {
            return;
        }

        $authorizer = $event->getMessage()->getAuthorizer();

        $officialAccount = $this->authorizerService->transformToOfficialAccount($authorizer);

        $localMsg = ServerMessage::createFromMessage($message);
        $localMsg->setAccount($officialAccount);

        // 因为在这里我们也能拿到OpenID了，所以同时也要存库一次
        $localUser = $this->userService->updateUserByOpenId($officialAccount, $message['FromUserName']);

        // 分发事件
        $nextEvent = new WechatOfficialAccountServerMessageRequestEvent();
        $nextEvent->setMessage($localMsg);
        $nextEvent->setAccount($officialAccount);
        $nextEvent->setUser($localUser);
        $this->eventDispatcher->dispatch($nextEvent);
    }
}
