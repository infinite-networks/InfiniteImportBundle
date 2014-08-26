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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Infinite\ImportBundle\Entity\Repository\ImportRepository")
 */
class Import
{
    const STATUS_ERRORS = 'infinite_import.errors';
    const STATUS_FINISHED = 'infinite_import.finished';
    const STATUS_IMPORTING = 'infinite_import.importing';
    const STATUS_NEEDS_PROCESSING = 'infinite_import.needs_processing';
    const STATUS_READY_TO_START = 'infinite_import.ready_to_start';
    const STATUS_STALLED = 'infinite_import.stalled';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * Not mapped. A property used by the importer to determine if it should abort
     * importing any more rows.
     *
     * @var bool
     */
    protected $abort = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $dateFinished;

    /**
     * Stores when the import was marked to be started.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $dateStarted;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $dateUploaded;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $errors = false;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Infinite\ImportBundle\Entity\ImportFieldMetadata",
     *   mappedBy="import",
     *   cascade={"persist"},
     *   orphanRemoval=true
     * )
     * @var ImportFieldMetadata
     */
    protected $fieldMetadata;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $filename;

    /**
     * Cached property.
     *
     * @var \Infinite\ImportBundle\Entity\ImportLine
     */
    protected $firstLine;

    /**
     * Stores the heartbeat from the importing process.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $heartbeat;

    /**
     * @ORM\JoinTable(joinColumns={@ORM\JoinColumn(onDelete="CASCADE")})
     * @ORM\OneToMany(
     *   targetEntity="Infinite\ImportBundle\Entity\ImportLine",
     *   mappedBy="import",
     *   cascade={"persist"},
     *   fetch="EXTRA_LAZY"
     * )
     * @var ImportLine[]
     */
    protected $lines;

    /**
     * Stores an aggregate count of how many rows have been processed.
     *
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $linesProcessed = 0;

    /**
     * @ORM\Column(type="array")
     * @var array
     */
    protected $metadata = array();

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $numLines = 0;

    /**
     * Stores a processor key if the import has been started with a specific processor.
     *
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $processorKey;

    /**
     * Stores if this import is running. The interface will only let a single import
     * run at a time but it is possible to run more than one from background processes
     * if necessary.
     *
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $running = false;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $size = 0;

    /**
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="Symfony\Component\Security\Core\User\UserInterface")
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $warnings = false;

    public function __construct()
    {
        $this->dateUploaded = new \DateTime;
        $this->fieldMetadata = new ArrayCollection();
        $this->lines = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function addFieldMetadata(ImportFieldMetadata $metadata)
    {
        $this->fieldMetadata->add($metadata);
        $metadata->setImport($this);
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFinished()
    {
        return $this->dateFinished;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateStarted()
    {
        return $this->dateStarted;
    }

    /**
     * @return mixed
     */
    public function getDateUploaded()
    {
        return $this->dateUploaded;
    }

    /**
     * Returns any lines from the import that had errors.
     *
     * @return \Infinite\ImportBundle\Entity\ImportLine[]
     */
    public function getErrorLines()
    {
        $criteria = Criteria::create();
        $e = $criteria->expr();
        $criteria->andWhere($e->eq('error', 1));

        return $this->lines->matching($criteria);
    }

    /**
     * @return \Infinite\ImportBundle\Entity\ImportFieldMetadata[]
     */
    public function getFieldMetadata()
    {
        return $this->fieldMetadata;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return array|ImportLine
     */
    public function getFirstLine()
    {
        if (!$this->firstLine) {
            $this->firstLine = $this->getLines()->slice(0, 1)[0];
        }

        return $this->firstLine;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @return int
     */
    public function getLinesProcessed()
    {
        return $this->linesProcessed;
    }

    /**
     * @return mixed
     */
    public function getNumLines()
    {
        return $this->numLines;
    }

    /**
     * @return string
     */
    public function getProcessorKey()
    {
        return $this->processorKey;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    public function getStatus()
    {
        if ($this->dateFinished) {
            if ($this->errors) {
                return static::STATUS_ERRORS;
            }

            return static::STATUS_FINISHED;
        }

        if (!$this->processorKey) {
            return static::STATUS_NEEDS_PROCESSING;
        }

        if (!$this->dateStarted) {
            return static::STATUS_READY_TO_START;
        }

        if (!$this->checkHeartbeat()) {
            return static::STATUS_STALLED;
        }

        return static::STATUS_IMPORTING;
    }

    public function incLinesProcessed($inc)
    {
        $this->linesProcessed += $inc;
    }

    public function checkHeartbeat(\DateTime $check = null)
    {
        if (!$this->getHeartbeat()) {
            return true;
        }

        if (null === $check) {
            $check = new \DateTime;
        }

        $check = clone $check;
        $check->modify('-2 minutes');

        return $check < $this->getHeartbeat();
    }

    /**
     * @return \DateTime
     */
    public function getHeartbeat()
    {
        return $this->heartbeat;
    }

    /**
     * @return \Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns any lines from the import that had warnings.
     *
     * @return \Infinite\ImportBundle\Entity\ImportLine[]
     */
    public function getWarningLines()
    {
        $criteria = Criteria::create();
        $e = $criteria->expr();
        $criteria->andWhere($e->eq('warning', 1));

        return $this->lines->matching($criteria);
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function hasWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return boolean
     */
    public function isAbort()
    {
        return $this->abort;
    }

    /**
     * @return boolean
     */
    public function isRunning()
    {
        return $this->running;
    }

    public function populateFromFile(File $file)
    {
        $this->size = $file->getSize();
        $this->filename = $file instanceof UploadedFile ?
            $file->getClientOriginalName() :
            $file->getFilename();
    }

    /**
     * @param boolean $abort
     */
    public function setAbort($abort)
    {
        $this->abort = $abort;
    }

    /**
     * @param \DateTime|null $dateFinished
     */
    public function setDateFinished($dateFinished)
    {
        $this->dateFinished = $dateFinished;
    }

    /**
     * @param \DateTime|null $dateStarted
     */
    public function setDateStarted(\DateTime $dateStarted = null)
    {
        $this->dateStarted = $dateStarted;
    }

    /**
     * @param mixed $dateUploaded
     */
    public function setDateUploaded($dateUploaded)
    {
        $this->dateUploaded = $dateUploaded;
    }

    /**
     * @param boolean $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param \DateTime $heartbeat
     */
    public function setHeartbeat(\DateTime $heartbeat = null)
    {
        $this->heartbeat = $heartbeat;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param ImportLine[] $lines
     */
    public function setLines($lines)
    {
        if (!$lines instanceof Collection) {
            $lines = new ArrayCollection($lines);
        }

        $this->lines = $lines;
        $this->numLines = count($lines);

        foreach ($lines as $line) {
            $line->setImport($this);
        }
    }

    /**
     * @param int $linesProcessed
     */
    public function setLinesProcessed($linesProcessed)
    {
        if ($linesProcessed > $this->numLines) {
            $linesProcessed = $this->numLines;
        }

        $this->linesProcessed = $linesProcessed;
    }

    /**
     * @param string $processorKey
     */
    public function setProcessorKey($processorKey)
    {
        $this->processorKey = $processorKey;
    }

    /**
     * @param boolean $running
     */
    public function setRunning($running)
    {
        $this->running = $running;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @param boolean $warnings
     */
    public function setWarnings($warnings)
    {
        $this->warnings = $warnings;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
