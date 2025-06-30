<?php

namespace WechatOpenPlatformBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WechatOpenPlatformBundle\DependencyInjection\WechatOpenPlatformExtension;

class WechatOpenPlatformExtensionTest extends TestCase
{
    private $extension;
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new WechatOpenPlatformExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证扩展可以正常加载
        $this->assertInstanceOf(ContainerBuilder::class, $this->container);
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('wechat_open_platform', $this->extension->getAlias());
    }
}