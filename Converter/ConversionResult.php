<?php

/**
 * This file is part of the ibms.tim project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Converter;

class ConversionResult
{
    /**
     * Stores any errors.
     *
     * @var array
     */
    public $errors = array();

    /**
     * If the conversion has headers, this has the array of the headers used in the
     * import.
     *
     * @var array|null
     */
    public $headers;

    /**
     * Stores an array of successfully processed lines.
     *
     * @var \Infinite\ImportBundle\Entity\ImportLine[]
     */
    public $lines;

    /**
     * Adds a failure to the result.
     *
     * @param array $rawRow
     * @param string $message
     */
    public function addFailure(array $rawRow, $message)
    {
        $this->errors[] = array(
            'row' => $rawRow,
            'error' => $message
        );
    }
}
