<?php

namespace WechatOpenPlatformBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use WechatOpenPlatformBundle\Controller\AuthCallbackController;
use WechatOpenPlatformBundle\Controller\AuthIndexController;
use WechatOpenPlatformBundle\Controller\OAuthBaseInfoController;
use WechatOpenPlatformBundle\Controller\ServerCallbackController;

#[AutoconfigureTag('routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function autoload(): RouteCollection
    {
        $collection = new \Symfony\Component\Routing\RouteCollection();

        $collection->addCollection($this->controllerLoader->load(AuthCallbackController::class));
        $collection->addCollection($this->controllerLoader->load(AuthIndexController::class));
        $collection->addCollection($this->controllerLoader->load(OAuthBaseInfoController::class));
        $collection->addCollection($this->controllerLoader->load(ServerCallbackController::class));

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }
}