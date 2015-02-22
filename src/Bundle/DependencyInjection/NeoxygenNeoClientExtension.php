<?php

namespace Neoxygen\NeoClient\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NeoxygenNeoClientExtension extends NeoClientExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        return parent::load($configs, $container);
    }

    public function getAlias()
    {
        return 'neoxygen_neo_client';
    }
}
