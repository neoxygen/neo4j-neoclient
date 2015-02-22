<?php

namespace Neoxygen\NeoClient\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass;
use Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass;

class NeoxygenNeoClientBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NeoClientExtensionsCompilerPass());
        $container->addCompilerPass(new ConnectionRegistryCompilerPass());
    }
}
