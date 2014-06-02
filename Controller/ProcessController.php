<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Controller;

use Infinite\ImportBundle\View\ProcessListView;
use Infinite\ImportBundle\View\ProcessProcessView;
use Infinite\ImportBundle\View\ProcessStartView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles the processing of files that have already been uploaded and converted to the
 * intermediate format.
 */
class ProcessController extends BaseController
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public $doctrine;

    /**
     * @var \Infinite\ImportBundle\Processor\ProcessorFactory
     */
    public $processorFactory;

    /**
     * Lists any uploads that have not yet been processed.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $view = new ProcessListView;
        $view->imports = $this->getRepository()->findAllForProcessing();

        return $this->templating->renderResponse('InfiniteImportBundle:Process:list.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * Marks the import ready to be processed, filling out metadata for anything the
     * processor might need to process the file.
     *
     * @param  Request                                    $request
     * @param  int                                        $id
     * @param  string                                     $processor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function processAction(Request $request, $id, $processor)
    {
        $import = $this->getImport($id);
        $processor = $this->processorFactory->getProcessor($processor);

        $command = $processor->buildCommand($import);
        $form = $processor->getProcessForm($request, $command);

        if ($form->isSubmitted() and $form->isValid()) {
            $processor->process($command);

            $this->doctrine->persist($import);
            $this->doctrine->flush();

            return new RedirectResponse($this->router->generate('infinite_import_view', array(
                'id' => $import->getId()
            )));
        }

        $view = new ProcessProcessView;
        $view->import = $command->import;
        $view->processor = $command->processor;
        $view->form = $form->createView();

        return $this->templating->renderResponse('InfiniteImportBundle:Process:process.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * Starts the processing of an actual upload by presenting the user with a list of
     * processors available.
     */
    public function startAction($id)
    {
        $import = $this->getImport($id);

        $view = new ProcessStartView;
        $view->import = $import;
        $view->processors = $this->processorFactory->getProcessors($import);

        if (count($view->processors) == 1) {
            $processor = reset($view->processors);

            return new RedirectResponse($this->router->generate('infinite_import_process_process', array(
                'id' => $import->getId(),
                'processor' => $processor->getKey()
            )));
        }

        return $this->templating->renderResponse('InfiniteImportBundle:Process:start.html.twig', array(
            'data' => $view,
        ));
    }

    /**
     * @param  int                                                           $id
     * @return \Infinite\ImportBundle\Entity\Import
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getImport($id)
    {
        $import = $this->getRepository()->find($id);
        if (!$import) {
            throw new NotFoundHttpException(sprintf('Import with id %d not found', $id));
        }

        return $import;
    }

    /**
     * @return \Infinite\ImportBundle\Entity\Repository\ImportRepository
     */
    private function getRepository()
    {
        return $this->doctrine->getRepository('Infinite\ImportBundle\Entity\Import');
    }
}
