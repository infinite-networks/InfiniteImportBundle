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

use Infinite\ImportBundle\Entity\ImportLine;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvConverter implements ConverterInterface
{
    /**
     * Converts each line of the file into the intermediate representation.
     *
     * @param File $file
     * @param bool $hasHeaders
     * @return ConversionResult
     */
    public function convert(File $file, $hasHeaders)
    {
        $fp = $file->openFile('r');
        $result = new ConversionResult;

        $i = 0;
        $columnCount = null;
        while ($rawLine = $fp->fgetcsv()) {
            if (!$rawLine or reset($rawLine) === null) {
                continue;
            }

            if (null === $columnCount) {
                $columnCount = count($rawLine);
            } else {
                if ($columnCount !== count($rawLine)) {
                    $result->addFailure(
                        $rawLine,
                        sprintf('Encountered an invalid number of columns for line %d', $i)
                    );

                    break;
                }
            }

            if ($hasHeaders and !$result->headers) {
                $result->headers = array_map('trim', $rawLine);

                continue;
            }

            try {
                foreach ($rawLine as &$rawColumn) {
                    $rawColumn = $this->sanitiseValue($rawColumn);
                }

                if ($hasHeaders) {
                    $rawLine = array_combine($result->headers, $rawLine);
                }

                $line = new ImportLine;
                $line->setData($rawLine);
                $line->setLine(++$i);

                $result->lines[] = $line;
            } catch (\Exception $e) {
                $result->addFailure($rawLine, $e->getMessage());

                break;
            }
        }

        return $result;
    }

    /**
     * Checks if this converter supports a given file.
     *
     * @param File $file
     * @return bool
     */
    public function supports(File $file)
    {
        $extension = $file instanceof UploadedFile ?
            $file->getClientOriginalExtension() :
            $file->getExtension();

        return 'csv' === $extension;
    }

    /**
     * Sanitises values from a CSV file.
     *
     * @param string $value
     * @return string
     */
    private function sanitiseValue($value)
    {
        // Handle poorly encoded documents. We always use UTF8
        $value = utf8_encode($value);

        // Remove the = and " from a CSV value like ="VALUE", which can be used to trick
        // Excel into keeping numbers sanely.
        $value = preg_replace('/^="(.*)"$/', '\1', $value);

        // Trim whitespace and quotes from around values.
        $value = trim($value, " \t\n\r\0\x0B\"'");

        return $value;
    }
}
