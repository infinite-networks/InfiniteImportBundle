<?php

/**
 * This file is part of the Infinite SSO project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Processor;

use Infinite\ImportBundle\Entity\Import;
use Infinite\ImportBundle\Entity\ImportFieldMetadata;
use Infinite\ImportBundle\Import\Importer;
use Infinite\ImportBundle\Import\ImporterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * The name of the form to be used for the process form type.
     */
    const PROCESS_FORM = 'infinite_import_process';

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ImporterInterface
     */
    private $importer;

    public function __construct(ImporterInterface $importer, FormFactoryInterface $formFactory)
    {
        $this->importer = $importer;
        $this->formFactory = $formFactory;
    }

    /**
     * Lets the processor determine if it should grant permission to the current user (as
     * supplied by the SecurityContext passed to this function).
     *
     * @param SecurityContextInterface $context
     * @return boolean
     */
    public function allowAccess(SecurityContextInterface $context)
    {
        return true;
    }

    /**
     * Builds a ProcessCommand object for the given Import
     *
     * @param  Import $import
     * @return ProcessCommand
     */
    public function buildCommand(Import $import)
    {
        $command = new ProcessCommand;
        $command->import = $import;
        $command->processor = $this;
        $command->fields = $this->buildCommandFields();

        return $command;
    }

    /**
     * Returns an array of ProcessCommandFields to be added to the command built
     * in buildCommand.
     *
     * @return ProcessCommandField[]
     */
    abstract protected function buildCommandFields();

    /**
     * Returns the size of the batch. If false is returned the importer will
     * not batch the import.
     *
     * @return int
     */
    public function getBatchSize()
    {
        return 20;
    }

    /**
     * Returns an Importer that can be used for this processor metadata.
     *
     * @return ImporterInterface
     */
    public function getImporter()
    {
        return $this->importer;
    }

    /**
     * Returns a form to be used for this process command.
     *
     * @param Request $request
     * @param ProcessCommand $command
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getProcessForm(Request $request, ProcessCommand $command)
    {
        $form = $this->formFactory->create(static::PROCESS_FORM, $command);
        $form->handleRequest($request);

        return $form;
    }

    /**
     * Initiates or continues processing an import in progress.
     *
     * @param ProcessCommand $command
     */
    public function process(ProcessCommand $command)
    {
        $import = $command->import;
        $import->setMetadata($command->metadata);
        $import->getFieldMetadata()->clear();

        foreach ($command->fields as $commandField) {
            $field = new ImportFieldMetadata;
            $field->setField($commandField->name);
            $field->setPopulateWith($commandField->populateWith);
            $import->addFieldMetadata($field);
        }
    }

    /**
     * Determines if the supplied import is supported by this processor. The processor
     * should check that rows have enough information.
     * It should return one of the SUPPORT constants from this interface.
     *
     * @param Import $import
     * @return int
     */
    public function supports(Import $import)
    {
        return static::SUPPORT_UNKNOWN;
    }
}
