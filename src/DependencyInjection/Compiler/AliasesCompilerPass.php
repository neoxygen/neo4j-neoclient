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

class AliasesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('event_dispatcher', 'neoclient.event_dispatcher');
        $container->setAlias('logger', 'neoclient.logger');
    }
}
