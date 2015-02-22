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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class NeoClientExtensionsCompilerPass implements CompilerPassInterface
{
    private $registeredCommands = array();

    public function process(ContainerBuilder $container)
    {
        $extensions = $container->findTaggedServiceIds('neoclient.extension_class');
        $commandManager = $container->getDefinition('neoclient.command_manager');
        $httpClient = $container->getDefinition('neoclient.http_client');
        $requestBuilder = $container->getDefinition('neoclient.request_builder');
        $extManager = $container->getDefinition('neoclient.extension_manager');

        foreach ($extensions as $id => $params) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $commands = $class::getAvailableCommands();
            $extManager->addMethodCall(
                'addExtension',
                array($class)
            );

            foreach ($commands as $alias => $props) {
                if (array_key_exists($alias, $this->registeredCommands)) {
                    throw new \InvalidArgumentException(sprintf('The command with alias "%s" already exist', $alias));
                }

                $def = new Definition();
                $def->setClass($props['class']);
                $def->addArgument($httpClient);
                $def->addArgument($requestBuilder);
                $container->setDefinition(sprintf('neoclient.command.%s', $alias), $def);
                $commandManager->addMethodCall(
                    'registerCommand',
                    array($alias, $def)
                );
                $this->registeredCommands[$alias] = true;
            }
        }

        $customCommands = $container->findTaggedServiceIds('neoclient.custom_command');

        foreach ($customCommands as $id => $params) {
            $def = $container->getDefinition($id);
            $def->addArgument($httpClient);
            $class = $def->getClass();
            $commandManager->addMethodCall(
                'registerCommand',
                array($params[0][0], $def)
            );
        }
    }
}
