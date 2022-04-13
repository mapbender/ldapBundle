<?php
/**
 * Created by PhpStorm.
 * User: dpatzke
 * Date: 13.02.19
 * Time: 14:39
 */

namespace Mapbender\LDAPBundle\DependencyInjection\Factory;


use Mapbender\LDAPBundle\Security\Provider\MapbenderLdapBindAuthenticationProvider;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class MapbenderLDAPLoginFactory extends FormLoginFactory
{

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'security.authentication.provider.mbldap.' . $id;
        $providerDefinition = new Definition(MapbenderLdapBindAuthenticationProvider::class, array(
            new Reference($userProviderId),
            new Reference('security.user_checker.' . $id),
            $id,
            new Reference($config['service']),
            new Reference('security.encoder_factory'),
            $container->getParameterBag()->resolveValue('%ldap.user.dn%'),
            null,   // ???
            $container->getParameterBag()->resolveValue('%ldap.user.query%'),
        ));
        $container->setDefinition($providerId, $providerDefinition);

        return $providerId;
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
