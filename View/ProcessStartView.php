<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\View;

class ProcessStartView
{
    /**
     * The import to be processed.
     *
     * @var \Infinite\ImportBundle\Entity\Import
     */
    public $import;

    /**
     * An array of processors sorted by suitability for the import.
     *
     * @var \Infinite\ImportBundle\Processor\ProcessorInterface
     */
    public $processors = array();
}
