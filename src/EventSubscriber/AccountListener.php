<?php

namespace WechatOpenPlatformBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use WechatOpenPlatformBundle\Entity\Account;
use WechatOpenPlatformBundle\Entity\ServerMessage;
use WechatOpenPlatformBundle\Repository\AccountRepository;

#[AsEntityListener(event: Events::prePersist, method: 'ensureLowerCase', entity: Account::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'ensureLowerCase', entity: Account::class)]
#[AsEntityListener(event: Events::postPersist, method: 'saveComponentVerifyTicket', entity: ServerMessage::class)]
class AccountListener
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 微信有个大坑，页面显示的appId是大写的，回调是小写的
     */
    public function ensureLowerCase(Account $account): void
    {
        $account->setAppId(mb_strtolower($account->getAppId()));
    }

    /**
     * 当有消息时，我们检查下是否有我们关心的 ComponentVerifyTicket 有就保存起来
     */
    public function saveComponentVerifyTicket(ServerMessage $object): void
    {
        if (($object->getMessage()['InfoType'] ?? '') !== 'component_verify_ticket') {
            return;
        }

        $account = $object->getAccount();
        $account->setComponentVerifyTicket($object->getMessage()['ComponentVerifyTicket']);
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }
}
