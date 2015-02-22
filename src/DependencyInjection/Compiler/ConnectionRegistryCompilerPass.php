<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ConnectionRegistryCompilerPass implements CompilerPassInterface
{
    private $master;

    private $slaves = [];

    public function process(ContainerBuilder $container)
    {
        $connections = $container->findTaggedServiceIds('neoclient.registered_connection');
        $connectionManager = $container->findDefinition('neoclient.connection_manager');
        $fallbacks = $container->findTaggedServiceIds('neoclient.fallback_connection');

        foreach ($connections as $id => $params) {
            $def = $container->getDefinition($id);
            if ($def->hasTag('neoclient.ha_master')) {
                if (null !== $this->master) {
                    throw new \RuntimeException('Having two connections registered as master is not permitted');
                }
                $this->master = $def->getArgument(0);
            }
            if ($def->hasTag('neoclient.ha_slave')) {
                $this->slaves[] = $def->getArgument(0);
            }
            $connectionManager
                ->addMethodCall(
                    'registerConnection',
                    array($container->getDefinition($id))
                );
        }

        foreach ($fallbacks as $id => $params) {
            $connectionManager
                ->addMethodCall(
                    'setFallbackConnection',
                    array($params[0]['fallback_of'], $params[0]['connection_alias'])
                );
        }
        if (null !== $this->master) {
            $connectionManager
                ->addMethodCall(
                    'setMasterConnection',
                    array($this->master)
                );
        }

        if (!empty($this->slaves)) {
            $connectionManager
                ->addMethodCall(
                    'setSlaveConnections',
                    array($this->slaves)
                );
        }
    }
}
