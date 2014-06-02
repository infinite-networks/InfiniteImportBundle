<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SupportedFileType extends Constraint
{
    public $message = 'The file type is not supported.';

    public function validatedBy()
    {
        return 'infinite_import_supported_file';
    }
}
