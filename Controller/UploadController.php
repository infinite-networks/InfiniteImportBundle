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

use Infinite\ImportBundle\Form\Type\UploadCommandType;
use Infinite\ImportBundle\Processor\ProcessorFactory;
use Infinite\ImportBundle\Upload\UploadCommand;
use Infinite\ImportBundle\View\UploadCreateView;
use Infinite\ImportBundle\View\UploadUploadView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the uploading and processing of a file into an intermediate format. This
 * controller and related services will process different registered file types into
 * the bundle's intermediate format that can then be processed by registered processors
 * for the import.
 */
class UploadController extends BaseController
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public $doctrine;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    public $formFactory;

    /**
     * @var \Infinite\ImportBundle\Upload\Processor;
     */
    public $processor;

    /**
     * @var \Infinite\ImportBundle\Processor\ProcessorFactory
     */
    public $processorFactory;

    /**
     * Presents the user with an upload form to upload a new file to be processed for an
     * import. The file format will be validated by the bundle that it can be processed.
     *
     * @param Request $request
     * @return UploadUploadView
     */
    public function createAction(Request $request)
    {
        $view = new UploadCreateView;
        $view->processors = $this->processorFactory->getProcessors();

        return $this->templating->renderResponse('InfiniteImportBundle:Upload:create.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * Presents the user with an upload form to upload a new file to be processed for an
     * import. The file format will be validated by the bundle that it can be processed.
     *
     * @param Request $request
     * @return UploadUploadView
     */
    public function uploadAction(Request $request, $processorKey)
    {
        $command = new UploadCommand;
        $command->processor = $this->processorFactory->getProcessor($processorKey);
        $form = $this->getUploadForm($request, $command);

        $view = new UploadUploadView;
        $view->form = $form->createView();

        if ($form->isSubmitted() and $form->isValid()) {
            $result = $this->processor->process($command);

            if ($result->isValid()) {
                $this->doctrine->persist($result->import);
                $this->doctrine->flush();

                return new RedirectResponse($this->router->generate('infinite_import_process_process', array(
                    'id' => $result->import->getId(),
                    'processor' => $result->import->getProcessorKey(),
                )));
            }

            $view->result = $result;
        }

        return $this->templating->renderResponse('InfiniteImportBundle:Upload:upload.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * Builds a new Upload command form and binds the request object.
     *
     * @param Request $request
     * @param UploadCommand $command
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getUploadForm(Request $request, UploadCommand $command)
    {
        $form = $this->formFactory->create(UploadCommandType::class, $command);
        $form->handleRequest($request);

        return $form;
    }
}
