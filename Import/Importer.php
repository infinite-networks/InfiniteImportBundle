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

use Doctrine\ORM\EntityManager;
use Infinite\ImportBundle\Entity\Import;
use Infinite\ImportBundle\Processor\ProcessorFactory;
use Infinite\ImportBundle\Processor\ProcessorInterface;
use Symfony\Component\Validator\ValidatorInterface;

class Importer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Infinite\ImportBundle\Processor\ProcessorFactory
     */
    private $processorFactory;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface
     */
    private $validator;

    public function __construct(
        EntityManager $entityManager,
        ProcessorFactory $processorFactory,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->processorFactory = $processorFactory;
        $this->validator = $validator;
    }

    /**
     * Run an import.
     *
     * @param Import $import
     * @param callable $updateClosure
     */
    public function import(Import $import, $updateClosure = null)
    {
        $this->validate($import);

        $iterator = $this->getIterator($import);
        $processor = $this->processorFactory->getProcessor($import->getProcessorKey());

        $this->batchImport($iterator, $import, $processor, $updateClosure);
    }

    /**
     * @param \Infinite\ImportBundle\Entity\ImportLine[] $iterator
     * @param Import $import
     * @param ProcessorInterface $processor
     * @param callable $updateClosure
     */
    private function batchImport($iterator, Import $import, ProcessorInterface $processor, $updateClosure = null)
    {
        $importer = $processor->getImporter();

        set_time_limit(0);
        $i = 0;

        $import->setRunning(true);
        $import->setHeartbeat(new \DateTime);
        $this->entityManager->flush($import);

        foreach ($iterator as $line) {
            /** @var \Infinite\ImportBundle\Entity\ImportLine $line */
            $line = $line[0];

            $importer->importLine($import, $line);
            $line->setDateProcessed(new \DateTime);

            if ($line->isError()) {
                $import->setErrors(true);
            }
            if ($line->isWarning()) {
                $import->setWarnings(true);
            }

            $import->incLinesProcessed(1);
            if (null !== $updateClosure) {
                $updateClosure($import, $line);
            }

            if ($import->isAbort() or $this->shouldBatch($processor, ++$i)) {
                $import->setHeartbeat(new \DateTime);
                $this->entityManager->flush();
                $this->entityManager->clear();

                $import = $this->entityManager->merge($import);
                $importer->batchClean();
            }

            if ($import->isAbort()) {
                break;
            }
        }

        if (!$import->isAbort()) {
            $import->setLinesProcessed($import->getNumLines());
        }
        $import->setDateFinished(new \DateTime);
        $import->setRunning(false);
        $this->entityManager->flush();
    }

    private function getIterator(Import $import)
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('il')
            ->from('Infinite\ImportBundle\Entity\ImportLine', 'il')
            ->where('IDENTITY(il.import) = :importId')
            ->andWhere('il.dateProcessed IS NULL');

        $qb->setParameter('importId', $import->getId());

        return $qb->getQuery()->iterate();
    }

    /**
     * Have we done enough iterations to batch?
     *
     * @param ProcessorInterface $processor
     * @param int $i
     * @return bool
     */
    private function shouldBatch(ProcessorInterface $processor, $i)
    {
        $batchSize = $processor->getBatchSize();
        if ($batchSize === false) {
            return false;
        }

        return !($i % $batchSize);
    }

    /**
     * Checks if the import can be considered valid for the importer.
     *
     * @param Import $import
     * @throws \LogicException
     */
    private function validate(Import $import)
    {
        $errors = $this->validator->validate($import, array('import'));

        if ($errors->count()) {
            throw new \LogicException('Invalid import for processing');
        }
    }
}
