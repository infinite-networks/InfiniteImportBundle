<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a data point required for an import that can be mapped to a piece of data
 * supplied by an import.
 */
class ProcessCommandField
{
    /**
     * The name of the field.
     *
     * @var string
     */
    public $name;

    /**
     * The string to search a group of headers for to try to automatically populate from
     * each row.
     *
     * @var string
     */
    public $populateWith;

    /**
     * If this field must be present in an import.
     *
     * @var bool
     */
    public $required;

    public function __construct($name, $required = true, $populateWith = null)
    {
        $this->name = $name;
        $this->populateWith = $populateWith ?: $name;
        $this->required = $required;
    }

    /**
     * @Assert\Callback(methods={"isValid"})
     */
    public function isValid(ExecutionContextInterface $context)
    {
        if ($this->required && null === $this->populateWith) {
            $context->buildViolation('A required selection is missing')
                ->atPath('populatedWith')
                ->addViolation();
        }
    }

    public function getTranslationName()
    {
        return str_replace(' ', '_', $this->name);
    }
}
