<?php

namespace WechatOpenPlatformBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WechatOpenPlatformBundle\WechatOpenPlatformBundle;

class WechatOpenPlatformBundleTest extends TestCase
{
    private WechatOpenPlatformBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new WechatOpenPlatformBundle();
    }

    public function testExtendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testCanBeInstantiated(): void
    {
        $bundle = new WechatOpenPlatformBundle();
        $this->assertInstanceOf(WechatOpenPlatformBundle::class, $bundle);
    }

    public function testGetName(): void
    {
        $this->assertSame('WechatOpenPlatformBundle', $this->bundle->getName());
    }
}