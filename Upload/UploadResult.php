<?php

/**
 * This file is part of the ibms.tim project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Upload;

use Infinite\ImportBundle\Converter\ConversionResult;
use Infinite\ImportBundle\Entity\Import;

class UploadResult
{
    /**
     * Stores the result of the Conversion, which may or may not have been successful.
     *
     * @var ConversionResult
     */
    public $conversionResult;

    /**
     * The import entity built with data required to save.
     *
     * @var Import
     */
    public $import;

    public function __construct(Import $import, ConversionResult $conversionResult)
    {
        $this->conversionResult = $conversionResult;
        $this->import = $import;
    }

    /**
     * An import can be considered valid if the conversion was successful. This does not
     * mean that the import will succeed the processing steps.
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->conversionResult->errors;
    }
}
