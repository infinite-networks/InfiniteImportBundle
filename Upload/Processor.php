<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Upload;

use Infinite\ImportBundle\Entity\Import;
use Infinite\ImportBundle\Converter\ConverterFactory;
use Symfony\Component\Security\Core\SecurityContextInterface;

class Processor
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(ConverterFactory $converterFactory, SecurityContextInterface $securityContext)
    {
        $this->converterFactory = $converterFactory;
        $this->securityContext = $securityContext;
    }

    /**
     * Processes a new upload.
     *
     * 1) Inserts a record of a new upload.
     * 2) Parses the uploaded file into intermediate format and inserts it.
     *
     * @param UploadCommand $command
     * @return UploadResult
     */
    public function process(UploadCommand $command)
    {
        set_time_limit(0);

        $conversionResult = $this->converterFactory->convert($command->file, $command->hasHeaders);

        $import = new Import;
        $import->populateFromFile($command->file);
        $import->setLines($conversionResult->lines);
        $import->setUser($this->securityContext->getToken()->getUser());

        $result = new UploadResult($import, $conversionResult);

        return $result;
    }
}
