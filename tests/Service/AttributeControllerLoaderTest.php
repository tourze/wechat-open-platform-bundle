<?php

namespace WechatOpenPlatformBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use WechatOpenPlatformBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new AttributeControllerLoader();
    }

    public function testInheritance(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
        $this->assertInstanceOf(RoutingAutoLoaderInterface::class, $this->loader);
    }

    public function testSupports(): void
    {
        $this->assertFalse($this->loader->supports('any_resource'));
        $this->assertFalse($this->loader->supports('any_resource', 'any_type'));
    }

    public function testLoad(): void
    {
        $collection = $this->loader->load('resource');
        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testAutoload(): void
    {
        $collection = $this->loader->autoload();
        $this->assertInstanceOf(RouteCollection::class, $collection);
        
        // 验证控制器路由被加载
        $this->assertGreaterThan(0, $collection->count());
    }
}