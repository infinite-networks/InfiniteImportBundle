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

use Infinite\ImportBundle\View\ImportListView;
use Infinite\ImportBundle\View\ImportViewView;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportController extends BaseController
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public $doctrine;

    /**
     * @var \Infinite\ImportBundle\Import\ImportScheduler
     */
    public $importScheduler;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public $session;

    /**
     * List all imports and their statuses.
     *
     * @return ImportListView
     */
    public function listAction()
    {
        $qb = $this->getRepository()->queryAll();

        $view = new ImportListView;
        $view->imports = $qb->getQuery()->execute();

        return $this->templating->renderResponse('InfiniteImportBundle:Import:list.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * Marks an import as ready to start and run the import from the kernel.terminate
     * event.
     *
     * This is a POST only action that causes data changes.
     */
    public function startAction($id)
    {
        $import = $this->getImport($id);
        $import->setDateStarted(new \DateTime);

        $this->doctrine->persist($import);
        $this->doctrine->flush();
        $this->session->save();
        session_write_close();

        $configuration = $this->doctrine
            ->getConnection()
            ->getConfiguration();
        $configuration->setSQLLogger(null);

        $this->importScheduler->schedule($import);

        return RedirectResponse::create($this->router->generate('infinite_import_view', array(
            'id' => $import->getId()
        )));
    }

    /**
     * Views and shows information about a single Import.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($id)
    {
        $import = $this->getImport($id);

        $view = new ImportViewView;
        $view->import = $import;

        return $this->templating->renderResponse('InfiniteImportBundle:Import:view.html.twig', array(
            'data' => $view
        ));
    }

    /**
     * @param int $id
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
