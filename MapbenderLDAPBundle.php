<?php

namespace Mapbender\LDAPBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MapbenderLDAPBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $configLocator = new FileLocator(__DIR__ . '/Resources/config');
        $loader = new YamlFileLoader($container, $configLocator);
        $loader->load('services.yml');
        $container->addResource(new FileResource($configLocator->locate('services.yml')));
    }
}
