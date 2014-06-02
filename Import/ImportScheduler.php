<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Import;

use Infinite\ImportBundle\Entity\Import;
use Psr\Log\LoggerInterface;

class ImportScheduler
{
    /**
     * @var Importer
     */
    private $importer;

    /**
     * @var Import[]
     */
    private $imports = array();

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(Importer $importer, LoggerInterface $logger = null)
    {
        $this->importer = $importer;
        $this->logger = $logger;
    }

    public function process()
    {
        foreach ($this->imports as $import) {
            try {
                $this->importer->import($import);
            } catch (\Exception $e) {
                if (!$this->logger) {
                    throw $e;
                }

                $this->logger->critical($e->getMessage(), array(
                    'import_id' => $import->getId(),
                ));
            }
        }
    }

    /**
     * Schedules an import for processing in the kernel.terminate event.
     *
     * @param Import $import
     */
    public function schedule(Import $import)
    {
        $this->imports[] = $import;
    }
}
