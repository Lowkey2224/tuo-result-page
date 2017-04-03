<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * ResultFile.
 *
 * @ORM\Table(name="result_file")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\ResultFileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ResultFile extends AbstractBaseEntity
{
    const STATUS_NOT_IMPORTED        = 0;
    const STATUS_IMPORTED            = 1;
    const STATUS_IMPORTED_WITH_ERROR = 2;
    const STATUS_ERROR               = 3;

    public function __construct()
    {
        $this->status  = self::STATUS_NOT_IMPORTED;
        $this->results = new ArrayCollection();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var int
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var Result[]
     * @ORM\OneToMany(targetEntity="Result", mappedBy="sourceFile")
     */
    private $results;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $missions;

    /**
     * @var Result[]
     * @ORM\Column(type="string", nullable=true)
     */
    private $comment;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $guild;


    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $originalName;

    /**
     * @throws Exception If there is no valid State
     *
     * @return string
     */
    public function getStatusName()
    {
        switch ($this->status) {
            case self::STATUS_NOT_IMPORTED:
                return 'Not yet Imported';
            case self::STATUS_IMPORTED:
                return 'Successfully Imported';
            case self::STATUS_IMPORTED_WITH_ERROR:
                return 'Imported with Errors';
            case self::STATUS_ERROR:
                return 'Not Imported because of Errors';
            default:
                throw new Exception('File has Invalid State');
        }
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return ResultFile
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Result[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param Result[] $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * @return string
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * @param string $guild
     */
    public function setGuild($guild)
    {
        $this->guild = $guild;
    }

    /**
     * @return Result[]
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Result[] $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getMissions()
    {
        return $this->missions;
    }

    /**
     * @param string $missions
     */
    public function setMissions($missions)
    {
        $this->missions = $missions;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion(int $version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName(string $originalName)
    {
        $this->originalName = $originalName;
    }

}
