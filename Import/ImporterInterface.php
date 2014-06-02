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
use Infinite\ImportBundle\Entity\ImportLine;

interface ImporterInterface
{
    /**
     * This method is called when a batch has been imported and any clean up or
     * reinitialisation of variables is required for this processor.
     */
    public function batchClean();

    /**
     * This method gets called for each import line during an import.
     *
     * @param Import $import
     * @param ImportLine $line
     */
    public function importLine(Import $import, ImportLine $line);
}
