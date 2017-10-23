<?php

/**
 * This file is part of ImportBundle
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Command;

use Infinite\ImportBundle\Entity\Import;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Cleans out data that is no longer needed
 */
class CleanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('infinite:import:clean')
            ->setDescription('Cleans up old data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $imports = $this->getContainer()->get('doctrine')
            ->getRepository(Import::class)
            ->findBy([
                'status' => Import::STATUS_FINISHED,
            ]);

        $killLines = <<<SQL
DELETE il FROM ImportLine il
WHERE il.import_id = :importId
SQL;

        $filesystem = new Filesystem();
        $connection = $this->getContainer()->get('database_connection');
        $stmt = $connection->prepare($killLines);

        /** @var Import $import */
        foreach ($imports as $import) {
            $stmt->bindValue('importId', $import->getId());
            $stmt->execute();

            if ($import->getAdditionalFilePath()) {
                $filesystem->remove($import->getAdditionalFilePath());
            }

            $import->setDateCleaned(new \DateTime());
        }
    }
}
