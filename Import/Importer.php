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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Infinite\ImportBundle\Entity\Import;
use Infinite\ImportBundle\Entity\ImportLine;
use Infinite\ImportBundle\Processor\ProcessorFactory;
use Infinite\ImportBundle\Processor\ProcessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Importer
{
    private $managerRegistry;
    private $processorFactory;
    private $validator;

    public function __construct(
        ManagerRegistry $managerRegistry,
        ProcessorFactory $processorFactory,
        ValidatorInterface $validator
    ) {
        $this->processorFactory = $processorFactory;
        $this->validator = $validator;
        $this->managerRegistry = $managerRegistry;
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
     * @param ImportLine[]|IterableResult $iterator
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
        $this->getEm()->flush($import);

        $importer->batchClean($import, $i);

        foreach ($iterator as $line) {
            /** @var ImportLine $line */
            $line = $line[0];

            try {
                $importer->importLine($import, $line);
                $line->setDateProcessed(new \DateTime);
            } catch (\Exception $e) {
                $em = $this->getEm();
                // Ensure the batch doesnt write anything.
                $em->clear();
                $em = $this->getEm();
                /** @var ImportLine $line */
                $line = $em->merge($line);
                /** @var Import $import */
                $import = $em->merge($import);

                $line->setError(true);
                $line->addLog('A fatal error has occurred with processing this import.');
                $line->addLog(sprintf('%s: %s', get_class($e), $e->getMessage()));
                $import->setAbort(true);
                $import->setErrors(true);
                $import->setRunning(false);
                $em->persist($import);
                $em->persist($line);
                $em->flush();

                return;
            }

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

                $em = $this->getEm();
                $importer->afterBatch();
                $em->flush();
                $em->clear();

                $import = $em->merge($import);
                $importer->batchClean($import, $i);
            }

            if ($import->isAbort()) {
                break;
            }
        }

        if (!$import->isAbort()) {
            $importer->finalise($import);
            $import->setLinesProcessed($import->getNumLines());
        }
        $import->setDateFinished(new \DateTime);
        $import->setRunning(false);

        $em = $this->getEm();
        $importer->afterBatch();
        $em->flush();
    }

    private function getIterator(Import $import)
    {
        $qb = $this->getEm()->createQueryBuilder()
            ->select('il')
            ->from(ImportLine::class, 'il')
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

    /**
     * @return EntityManager
     */
    private function getEm()
    {
        return $this->managerRegistry->getManagerForClass(Import::class);
    }
}
