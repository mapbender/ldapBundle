<?php

namespace  Mapbender\LDAPBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverwriteIdentitiesProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter("fom.identities.provider.class", "Mapbender\LDAPBundle\Security\Provider\LDAPIdentitiesProvider");

    }
}
