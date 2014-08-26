<?php

/**
 * This file is part of the Infinite SSO project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Import;

use Infinite\ImportBundle\Entity\Import;

abstract class AbstractImporter implements ImporterInterface
{
    /**
     * Cache for a metadata configuration array.
     *
     * @var array
     */
    private $config;

    /**
     * This method is called when a batch has been imported and any clean up or
     * reinitialisation of variables is required for this processor.
     */
    public function batchClean(Import $import)
    {
        // No batch cleaning required for a basic importer.
    }

    public function finalise(Import $import)
    {
        // No finalisation required for a base importer
    }

    /**
     * Returns field mappings.
     *
     * @param Import $import
     * @return array
     */
    protected function getConfig(Import $import)
    {
        if (!$this->config) {
            $this->config = array();

            foreach ($import->getFieldMetadata() as $metadata) {
                $this->config[$metadata->getField()] = $metadata->getPopulateWith();
            }
        }

        return $this->config;
    }
}
