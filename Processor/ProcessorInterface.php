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
use Infinite\ImportBundle\Import\ImporterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

interface ProcessorInterface
{
    /**
     * This processor can process the file without any intervention from the user to
     * define specific columns. IE: the file had headers and the header names match what
     * this importer expects.
     */
    const SUPPORT_SUPPORTED = 2;

    /**
     * This processor is unable to process this file as it does not have enough
     * information to be able to process it. This may mean that if there are headers, that
     * there are missing required fields.
     */
    const SUPPORT_NOTSUPPORTED = 0;

    /**
     * The processor is unable to determine if the file is supported, and it might be
     * supported if the user can tell the processor which fields in the import match
     * fields required by the processor.
     */
    const SUPPORT_UNKNOWN = 1;

    /**
     * Lets the processor determine if it should grant permission to the current user (as
     * supplied by the SecurityContext passed to this function).
     *
     * @param SecurityContextInterface $context
     * @return boolean
     */
    public function allowAccess(SecurityContextInterface $context);

    /**
     * Builds a ProcessCommand object for the given Import
     *
     * @param  Import         $import
     * @return ProcessCommand
     */
    public function buildCommand(Import $import);

    /**
     * Returns an Importer that can be used for this processor metadata.
     *
     * @return ImporterInterface
     */
    public function getImporter();

    /**
     * Returns the processor key. Should be unique for the entire application.
     *
     * @return string
     */
    public function getKey();

    /**
     * Returns a form to be used for this process command.
     *
     * @param Request $request
     * @param ProcessCommand $command
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getProcessForm(Request $request, ProcessCommand $command);

    /**
     * Initiates or continues processing an import in progress.
     *
     * @param ProcessCommand $command
     */
    public function process(ProcessCommand $command);

    /**
     * Determines if the supplied import is supported by this processor. The processor
     * should check that rows have enough information.
     *
     * It should return one of the SUPPORT constants from this interface.
     *
     * @param Import $import
     * @return int
     */
    public function supports(Import $import);
}
