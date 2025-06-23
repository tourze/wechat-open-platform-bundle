<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 临时的 AuthLog 实体，用于解决依赖问题
 */
#[ORM\Entity]
#[ORM\Table(name: 'wechat_open_platform_auth_log', options: ['comment' => '授权日志'])]
class AuthLog implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '类型'])]
    private string $type;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'OpenID'])]
    private string $openId;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '原始数据'])]
    private string $rawData;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): self
    {
        $this->openId = $openId;
        return $this;
    }

    public function getRawData(): string
    {
        return $this->rawData;
    }

    public function setRawData(string $rawData): self
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('AuthLog #%s', $this->id ?? 'new');
    }
}