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

use Infinite\ImportBundle\Converter\ConverterFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SupportedFileTypeValidator extends ConstraintValidator
{
    /**
     * @var \Infinite\ImportBundle\Converter\ConverterFactory
     */
    private $converterFactory;

    public function __construct(ConverterFactory $converterFactory)
    {
        $this->converterFactory = $converterFactory;
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof File) {
            throw new UnexpectedTypeException($value, File::class);
        }

        if (!$this->converterFactory->supports($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
