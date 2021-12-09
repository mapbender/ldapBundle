<?php

namespace Mapbender\LDAPBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverwriteIdentitiesProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter("fom.identities.provider.class", "Mapbender\LDAPBundle\Security\Provider\LDAPIdentitiesProvider");
        $container->register('fom.identities.provider', "Mapbender\LDAPBundle\Security\Provider\LDAPIdentitiesProvider")
            ->addArgument(new Reference('doctrine'))
            ->addArgument(new Reference('fom.ldap_user_identities_provider'))
            ->addArgument('%fom.user_entity%')
            ->addArgument(new Reference('service_container'))
        ;
    }
}
