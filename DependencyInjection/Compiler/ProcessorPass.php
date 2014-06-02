<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProcessorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('infinite_import.processor_factory')) {
            return;
        }

        $processors = array();
        foreach (array_keys($container->findTaggedServiceIds('infinite_import.processor')) as $id) {
            $processors[] = new Reference($id);
        }

        $container->getDefinition('infinite_import.processor_factory')->replaceArgument(1, $processors);
    }
}
