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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProcessorFactory
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors = array();

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(SecurityContextInterface $securityContext, array $processors)
    {
        foreach ($processors as $processor) {
            $this->processors[$processor->getKey()] = $processor;
        }

        $this->securityContext = $securityContext;
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
        if (!$processor->allowAccess($this->securityContext)) {
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
            if ($processor->allowAccess($this->securityContext)) {
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
