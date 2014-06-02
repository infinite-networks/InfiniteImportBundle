<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class InfiniteImportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // $configuration = $this->getConfiguration($configs, $container);
        // $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.xml');
        $loader->load('converter.xml');
        $loader->load('import.xml');
        $loader->load('processor.xml');
        $loader->load('upload.xml');
        $loader->load('validator.xml');
    }
}
