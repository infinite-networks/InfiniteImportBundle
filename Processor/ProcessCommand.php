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

use Symfony\Component\Validator\Constraints as Assert;

class ProcessCommand
{
    /**
     * All field information to be presented to the user should be populated into this
     * property.
     *
     * @Assert\Valid
     * @var ProcessCommandField[]
     */
    public $fields;

    /**
     * @var \Infinite\ImportBundle\Entity\Import
     */
    public $import;

    /**
     * An array of metadata that any processor may fill with its form types, and then
     * access it while processing this into an Import.
     *
     * @var array
     */
    public $metadata = array();

    /**
     * @var \Infinite\ImportBundle\Processor\ProcessorInterface
     */
    public $processor;
}
