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

class ProcessProcessView
{
    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    public $form;

    /**
     * The import to be processed.
     *
     * @var \Infinite\ImportBundle\Entity\Import
     */
    public $import;

    /**
     * @var \Infinite\ImportBundle\Processor\ProcessorInterface
     */
    public $processor;
}
