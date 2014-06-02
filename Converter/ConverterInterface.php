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

interface ConverterInterface
{
    /**
     * Performs conversion on the supplied file, returning a ConversionResult object with
     * the results of the conversion.
     *
     * The converter must handle conversion of data to an appropriate format, stripping
     * quirks of each importable format (like dropping surrounding quotes or UTF8
     * conversion)
     *
     * The converter may fail half way through processing a file and can return an
     * incomplete ConversionResult with appropriate properties set for its failure.
     *
     * @param File $file
     * @param bool $hasHeaders
     * @return \Infinite\ImportBundle\Converter\ConversionResult
     */
    public function convert(File $file, $hasHeaders);

    /**
     * Tests if the file is supported by this converter.
     *
     * @param File $file
     * @return bool
     */
    public function supports(File $file);
}
