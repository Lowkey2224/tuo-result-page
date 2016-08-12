<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Card
 *
 * @ORM\Table(name="card")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\CardRepository")
 */
class Card
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="Attack", type="integer")
     */
    private $attack;

    /**
     * @var int
     *
     * @ORM\Column(name="Defense", type="integer")
     */
    private $defense;

    /**
     * @var int
     *
     * @ORM\Column(name="Delay", type="integer")
     */
    private $delay;

    /**
     * @var string
     *
     * @ORM\Column(name="Picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var
     * @ORM\Column(type="array", nullable=true)
     */
    private $skills;

    /**
     * @var CardFile
     * @ORM\ManyToOne(targetEntity="CardFile", inversedBy="cards")
     * @ORM\JoinColumn(referencedColumnName="id", name="cardfile_id")
     */
    private $cardFile;

    public function __construct()
    {
        $this->defense = 0;
        $this->delay = 0;
        $this->attack = 0;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set attack
     *
     * @param integer $attack
     *
     * @return Card
     */
    public function setAttack($attack)
    {
        $this->attack = (int)$attack;

        return $this;
    }

    /**
     * Get attack
     *
     * @return int
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     *
     * @return Card
     */
    public function setDefense($defense)
    {
        $this->defense = (int)$defense;

        return $this;
    }

    /**
     * Get defense
     *
     * @return int
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * Set delay
     *
     * @param integer $delay
     *
     * @return Card
     */
    public function setDelay($delay)
    {
        $this->delay = (int)$delay;

        return $this;
    }

    /**
     * Get delay
     *
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Card
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return CardFile
     */
    public function getCardFile()
    {
        return $this->cardFile;
    }

    /**
     * @param CardFile $cardFile
     */
    public function setCardFile($cardFile)
    {
        $this->cardFile = $cardFile;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * @param mixed $skills
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
    }
}
