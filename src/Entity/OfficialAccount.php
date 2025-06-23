<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 临时的 OfficialAccount 实体，用于解决依赖问题
 */
#[ORM\Entity]
#[ORM\Table(name: 'wechat_open_platform_official_account', options: ['comment' => '公众号账号'])]
class OfficialAccount implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'AppID'])]
    private string $appId;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '第三方平台AppID'])]
    private ?string $componentAppId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): self
    {
        $this->appId = $appId;
        return $this;
    }

    public function getComponentAppId(): ?string
    {
        return $this->componentAppId;
    }

    public function setComponentAppId(?string $componentAppId): self
    {
        $this->componentAppId = $componentAppId;
        return $this;
    }

    public function __toString(): string
    {
        return $this->appId ?? '';
    }
}