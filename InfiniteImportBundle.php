<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle;

use Infinite\ImportBundle\DependencyInjection\Compiler\ConverterPass;
use Infinite\ImportBundle\DependencyInjection\Compiler\ProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class InfiniteImportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConverterPass());
        $container->addCompilerPass(new ProcessorPass());
    }
}
