<?php

/**
 * This file is part of the ImportBundle project.
 *
 * (c) Infinite Networks Pty Ltd <http://www.infinite.net.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infinite\ImportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ImportFieldMetadata
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE", onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Infinite\ImportBundle\Entity\Import", inversedBy="fieldMetadata")
     * @var Import
     */
    protected $import;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $field;

    /**
     * Stores the position or key of the data source that should populate this field, if
     * null, the field wont be populated/updated.
     *
     * @ORM\Column(type="string", nullable=true)
     * @var int|string|null
     */
    protected $populateWith;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param \Infinite\ImportBundle\Entity\Import $import
     */
    public function setImport(Import $import)
    {
        $this->import = $import;
    }

    /**
     * @return \Infinite\ImportBundle\Entity\Import
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param int|null|string $populateWith
     */
    public function setPopulateWith($populateWith)
    {
        $this->populateWith = $populateWith;
    }

    /**
     * @return int|null|string
     */
    public function getPopulateWith()
    {
        return $this->populateWith;
    }
}
