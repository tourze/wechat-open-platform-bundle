<?php

namespace WechatOpenPlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use WechatOfficialAccountBundle\Entity\AccessTokenAware;
use WechatOpenPlatformBundle\Repository\AuthorizerRepository;

/**
 * 因为一个公众号可能授权给多个应用，所以唯一索引会比较奇怪
 */
#[AsPermission(title: '授权应用')]
#[Deletable]
#[Editable]
#[ORM\Entity(repositoryClass: AuthorizerRepository::class)]
#[ORM\Table(name: 'wechat_open_platform_authorizer', options: ['comment' => '授权应用'])]
#[ORM\UniqueConstraint(name: 'wechat_open_platform_authorizer_idx_uniq', columns: ['account_id', 'app_id'])]
class Authorizer implements PlainArrayInterface, AccessTokenAware
{
    use TimestampableAware;
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[ListColumn(title: '授权开放平台应用')]
    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'authorizers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Account $account = null;

    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => 'AppID'])]
    private ?string $appId = null;

    /**
     * 在授权的公众号/小程序具备 API 权限时，才有此返回值
     */
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '接口调用令牌'])]
    private ?string $authorizerAccessToken = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $accessTokenExpireTime = null;

    /**
     * 刷新令牌（在授权的公众号具备 API 权限时，才有此返回值），刷新令牌主要用于第三方平台获取和刷新已授权用户的 authorizer_access_token。
     * 一旦丢失，只能让用户重新授权，才能再次拿到新的刷新令牌。用户重新授权后，之前的刷新令牌会失效.
     */
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '刷新令牌'])]
    private ?string $authorizerRefreshToken = null;

    #[TrackColumn]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '权限集列表'])]
    private array $funcInfo = [];

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'authorizer', targetEntity: ServerMessage::class)]
    private Collection $serverMessages;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    public function __construct()
    {
        $this->serverMessages = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
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

    public function getAuthorizerAccessToken(): ?string
    {
        return $this->authorizerAccessToken;
    }

    public function setAuthorizerAccessToken(?string $authorizerAccessToken): self
    {
        $this->authorizerAccessToken = $authorizerAccessToken;

        return $this;
    }

    public function getAuthorizerRefreshToken(): ?string
    {
        return $this->authorizerRefreshToken;
    }

    public function setAuthorizerRefreshToken(?string $authorizerRefreshToken): self
    {
        $this->authorizerRefreshToken = $authorizerRefreshToken;

        return $this;
    }

    public function getFuncInfo(): ?array
    {
        return $this->funcInfo;
    }

    public function setFuncInfo(?array $funcInfo): self
    {
        $this->funcInfo = $funcInfo;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getAccessTokenExpireTime(): ?\DateTimeInterface
    {
        return $this->accessTokenExpireTime;
    }

    public function setAccessTokenExpireTime(?\DateTimeInterface $accessTokenExpireTime): static
    {
        $this->accessTokenExpireTime = $accessTokenExpireTime;

        return $this;
    }public function retrievePlainArray(): array
    {
        return [
            'id' => $this->getId(),
            'appId' => $this->getAppId(),
        ];
    }

    public function getAccessToken(): ?string
    {
        return $this->getAuthorizerAccessToken();
    }

    public function getAccessTokenKeyName(): string
    {
        return 'access_token';
    }

    /**
     * @return Collection<int, ServerMessage>
     */
    public function getServerMessages(): Collection
    {
        return $this->serverMessages;
    }

    public function addServerMessage(ServerMessage $serverMessage): static
    {
        if (!$this->serverMessages->contains($serverMessage)) {
            $this->serverMessages->add($serverMessage);
            $serverMessage->setAuthorizer($this);
        }

        return $this;
    }

    public function removeServerMessage(ServerMessage $serverMessage): static
    {
        if ($this->serverMessages->removeElement($serverMessage)) {
            // set the owning side to null (unless already changed)
            if ($serverMessage->getAuthorizer() === $this) {
                $serverMessage->setAuthorizer(null);
            }
        }

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }
}
