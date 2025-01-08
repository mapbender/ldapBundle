<?php

namespace Mapbender\LDAPBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\DependencyInjection\Definition;

class MapbenderLDAPBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $configLocator = new FileLocator(__DIR__ . '/Resources/config');
        $loader = new YamlFileLoader($container, $configLocator);
        $loader->load('services.yml');
        $providerDefinition = new Definition(Adapter::class, array([
                'host' => '%ldap.host%',
                'port' => '%ldap.port%',
                'version' => '%ldap.version%',
                'encryption' => '%ldap.encryption%',
            ],
        ));
        $container->setDefinition('Symfony\Component\Ldap\Adapter\ExtLdap\Adapter', $providerDefinition);
    }
}
