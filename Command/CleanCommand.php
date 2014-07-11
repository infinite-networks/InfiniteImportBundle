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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleans out data that is no longer needed
 */
class CleanCommand extends ContainerAwareCommand
{
    /**
     * @see Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('infinite:import:clean')
            ->setDescription('Cleans up old data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = <<<SQL
DELETE il FROM ImportLine il
LEFT JOIN Import i ON (il.import_id = i.id)
WHERE
	i.dateFinished IS NOT NULL AND
	(
		il.dateProcessed IS NULL OR
		(
			il.error = 0 AND
			il.warning = 0
		)
	)
SQL;

        $connection = $this->getContainer()->get('database_connection');
        $connection->executeQuery($sql);
    }
}
