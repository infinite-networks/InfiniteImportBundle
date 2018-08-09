<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Processor;

use Infinite\ImportBundle\Entity\Import;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProcessorFactory
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors = array();
    private $checker;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(AuthorizationCheckerInterface $checker, array $processors)
    {
        foreach ($processors as $processor) {
            $this->processors[$processor->getKey()] = $processor;
        }

        $this->checker = $checker;
    }

    /**
     * @param  string                    $processorKey
     * @return ProcessorInterface
     * @throws \InvalidArgumentException
     */
    public function getProcessor($processorKey)
    {
        if (!array_key_exists($processorKey, $this->processors)) {
            throw new \InvalidArgumentException;
        }

        $processor = $this->processors[$processorKey];
        if (!$processor->allowAccess($this->checker)) {
            throw new AccessDeniedException;
        }

        return $processor;
    }

    /**
     * Returns all processors, optionally sorted by which processors indicate support
     * for an import.
     *
     * @param Import $import
     * @return ProcessorInterface[]
     */
    public function getProcessors(Import $import = null)
    {
        $processors = array();

        foreach ($this->processors as $processor) {
            if ($processor->allowAccess($this->checker)) {
                $processors[] = $processor;
            }
        }

        if ($import) {
            usort($processors, function (ProcessorInterface $a, ProcessorInterface $b) use ($import) {
                return $a->supports($import) > $b->supports($import);
            });
        }


        return $processors;
    }
}
