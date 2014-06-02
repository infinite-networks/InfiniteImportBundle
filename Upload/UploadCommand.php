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

/**
 * This command represents an upload activity where the file should then be processed into
 * the intermediate format.
 */
class UploadCommand
{
    /**
     * The uploaded file to be processed.
     *
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;

    /**
     * If the uploaded file has headers for the first row.
     *
     * @var bool
     */
    public $hasHeaders = true;
}
