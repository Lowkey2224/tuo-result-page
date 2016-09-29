<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CardFile
 *
 * @ORM\Table(name="card_file")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\CardFileRepository")
 */
class CardFile extends AbstractBaseEntity
{
    const STATUS_NOT_IMPORTED = 0;
    const STATUS_IMPORTED = 1;
    const STATUS_IMPORTED_WITH_ERROR = 2;


    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Card", mappedBy="cardFile")
     */
    private $cards;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $originalFileName;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status;

    public function __construct()
    {
        $this->status = 0;
        $this->cards = new ArrayCollection();
    }


    /**
     * Set content
     *
     * @param string $content
     *
     * @return CardFile
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
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @param ArrayCollection $cards
     */
    public function setCards($cards)
    {
        $this->cards = $cards;
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
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->originalFileName;
    }

    /**
     * @param string $originalFileName
     */
    public function setOriginalFileName($originalFileName)
    {
        $this->originalFileName = $originalFileName;
    }
}
