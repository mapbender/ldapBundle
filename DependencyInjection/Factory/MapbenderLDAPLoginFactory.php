<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 13.02.19
 * Time: 14:39
 */

namespace Mapbender\LDAPBundle\DependencyInjection\Factory;


use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class MapbenderLDAPLoginFactory extends FormLoginFactory
{

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.mbldap.'.$id;
        $container->setDefinition($provider, new DefinitionDecorator('security.authentication.provider.mbldap'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, new Reference('security.user_checker.'.$id))
            ->replaceArgument(2, $id)
            ->replaceArgument(3, new Reference($config['service']))
            ->replaceArgument(4, new Reference('security.encoder_factory'))
        ;

        return $provider;
    }



    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node
            ->children()
            ->scalarNode('service')->end()

        ;
    }

    public function getKey()
    {
        return 'mapbender-ldap';
    }

}
