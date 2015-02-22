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

class EventSubscribersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $subscribers = $container->findTaggedServiceIds('neoclient.service_event_subscriber');
        $ev = $container->getDefinition('neoclient.event_dispatcher');
        foreach ($subscribers as $id => $params) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $ev->addMethodCall(
                'addSubscriberService',
                array($id, $class)
            );
        }
    }
}
