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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs new, or reruns stalled imports. Will process a single import per run, and
 * should be run every few minutes from a cron. Will flake out if too many imports
 * are running at the same time.
 */
class RunImportsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('infinite:import:run')
            ->setDescription('Runs, or reruns any stalled or new import')
            ->addArgument('importId', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import = $this->getImportToRun($input);

        if ($import) {
            $progress = new ProgressBar($output, $import->getNumLines());
            $progress->start();

            /** @var \Infinite\ImportBundle\Import\Importer $importer */
            $importer = $this->getContainer()->get('infinite_import.import.importer');

            $progress->advance($import->getLinesProcessed());

            $importer->import($import, function () use ($progress) {
                $progress->advance();
            });

            $progress->finish();
        }
    }

    /**
     * Returns a new or stalled import to run for this iteration of the command.
     *
     * @return Import
     */
    private function getImportToRun(InputInterface $input)
    {
        $repository = $this->getContainer()->get('doctrine')->getRepository(Import::class);

        if ($id = $input->getArgument('importId')) {
            return $repository->find($id);
        }

        $imports = $repository->findStalledOrNewImports();

        return reset($imports);
    }
}
