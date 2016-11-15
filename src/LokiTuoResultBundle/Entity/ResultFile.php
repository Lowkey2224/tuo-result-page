<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ResultFile
 *
 * @ORM\Table(name="result_file")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\ResultFileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ResultFile extends AbstractBaseEntity
{
    const STATUS_NOT_IMPORTED = 0;
    const STATUS_IMPORTED = 1;
    const STATUS_IMPORTED_WITH_ERROR = 2;

    public function __construct()
    {
        $this->status = self::STATUS_NOT_IMPORTED;
        $this->results = new ArrayCollection();
    }


    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var integer
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
     * @ORM\Column(type="string")
     */
    private $guild;


    /**
     * Set content
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
     * Get content
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
}
