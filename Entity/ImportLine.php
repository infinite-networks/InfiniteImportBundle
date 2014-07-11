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
class ImportLine
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $data;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $dateProcessed;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $error = false;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Infinite\ImportBundle\Entity\Import", inversedBy="lines")
     * @var Import
     */
    protected $import;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $line;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $log = array();

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $warning = false;

    public function getId()
    {
        return $this->id;
    }

    public function addLog($log)
    {
        $this->log[] = $log;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \DateTime
     */
    public function getDateProcessed()
    {
        return $this->dateProcessed;
    }

    /**
     * @return mixed
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return boolean
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * @return boolean
     */
    public function isWarning()
    {
        return $this->warning;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param \DateTime $dateProcessed
     */
    public function setDateProcessed(\DateTime $dateProcessed = null)
    {
        $this->dateProcessed = $dateProcessed;
    }

    /**
     * @param boolean $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @param mixed $import
     */
    public function setImport($import)
    {
        $this->import = $import;
    }

    /**
     * @param mixed $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @param boolean $warning
     */
    public function setWarning($warning)
    {
        $this->warning = $warning;
    }
}
