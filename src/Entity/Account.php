<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOpenPlatformBundle\Repository\AccountRepository;

/**
 * @see https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/Before_Develop/component_verify_ticket.html
 */
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ORM\Table(name: 'wechat_open_platform_account', options: ['comment' => '开放平台应用'])]
class Account implements PlainArrayInterface, AccessTokenAware
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 120, unique: true, options: ['comment' => 'AppID'])]
    private ?string $appId = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => '应用Secret'])]
    private ?string $appSecret = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => 'Token'])]
    private ?string $token = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true, options: ['comment' => 'AES Key'])]
    private ?string $aesKey = null;

    /**
     * @var Collection<Authorizer>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Authorizer::class)]
    private Collection $authorizers;

    #[TrackColumn]
    #[ORM\Column(length: 120, nullable: true, options: ['comment' => 'ComponentVerifyTicket'])]
    private ?string $componentVerifyTicket = null;

    #[TrackColumn]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => 'ComponentAccessToken'])]
    private ?string $componentAccessToken = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'AccessToken过期时间'])]
    private ?\DateTimeInterface $componentAccessTokenExpireTime = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    public function __construct()
    {
        $this->authorizers = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): self
    {
        $this->appSecret = $appSecret;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAesKey(): ?string
    {
        return $this->aesKey;
    }

    public function setAesKey(?string $aesKey): self
    {
        $this->aesKey = $aesKey;

        return $this;
    }

    /**
     * @return Collection<int, Authorizer>
     */
    public function getAuthorizers(): Collection
    {
        return $this->authorizers;
    }

    public function addAuthorizer(Authorizer $authorizer): self
    {
        if (!$this->authorizers->contains($authorizer)) {
            $this->authorizers[] = $authorizer;
            $authorizer->setAccount($this);
        }

        return $this;
    }

    public function removeAuthorizer(Authorizer $authorizer): self
    {
        if ($this->authorizers->removeElement($authorizer)) {
            // set the owning side to null (unless already changed)
            if ($authorizer->getAccount() === $this) {
                $authorizer->setAccount(null);
            }
        }

        return $this;
    }

    public function getComponentVerifyTicket(): ?string
    {
        return $this->componentVerifyTicket;
    }

    public function setComponentVerifyTicket(?string $componentVerifyTicket): static
    {
        $this->componentVerifyTicket = $componentVerifyTicket;

        return $this;
    }

    public function getComponentAccessToken(): ?string
    {
        return $this->componentAccessToken;
    }

    public function setComponentAccessToken(?string $componentAccessToken): static
    {
        $this->componentAccessToken = $componentAccessToken;

        return $this;
    }

    public function getComponentAccessTokenExpireTime(): ?\DateTimeInterface
    {
        return $this->componentAccessTokenExpireTime;
    }

    public function setComponentAccessTokenExpireTime(?\DateTimeInterface $componentAccessTokenExpireTime): static
    {
        $this->componentAccessTokenExpireTime = $componentAccessTokenExpireTime;

        return $this;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'appId' => $this->getAppId(),
        ];
    }

    public function getAccessToken(): ?string
    {
        return $this->getComponentAccessToken();
    }

    public function getAccessTokenKeyName(): string
    {
        return 'component_access_token';
    }
}
