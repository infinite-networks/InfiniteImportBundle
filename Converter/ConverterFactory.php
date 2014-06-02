<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Converter;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Handles a conversion of a file, deferring to an actual converter that supports a given
 * file. The first registered converter that indicates it supports a file type will be
 * called for the conversion.
 */
class ConverterFactory
{
    /**
     * @var \Infinite\ImportBundle\Converter\ConverterInterface[]
     */
    private $converters = array();

    /**
     * @param \Infinite\ImportBundle\Converter\ConverterInterface[] $converters
     */
    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }

    /**
     * Runs a conversion and returns the result.
     *
     * @param File $file
     * @param Boolean $hasHeaders
     * @return \Infinite\ImportBundle\Converter\ConversionResult
     */
    public function convert(File $file, $hasHeaders)
    {
        $converter = $this->getConverter($file);

        return $converter->convert($file, $hasHeaders);
    }

    /**
     * A convenience method to determine if a file is supported by a registered Converter.
     *
     * @param File $file
     * @return bool
     */
    public function supports(File $file)
    {
        return $this->getConverter($file) !== null;
    }

    /**
     * Finds a converter for a file.
     *
     * @param File $file
     * @return ConverterInterface|null
     */
    private function getConverter(File $file)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($file)) {
                return $converter;
            }
        }

        return null;
    }
}
