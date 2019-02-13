<?php

namespace Mapbender\LDAPBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Mapbender\LDAPBundle\DependencyInjection\Compiler\OverwriteIdentitiesProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mapbender\LDAPBundle\DependencyInjection\Factory\MapbenderLDAPLoginFactory;



class MapbenderLDAPBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $extension = $container->getExtension('security');
        $container->addCompilerPass(new OverwriteIdentitiesProviderPass());
        $extension->addSecurityListenerFactory(new MapbenderLDAPLoginFactory());
    }

}
