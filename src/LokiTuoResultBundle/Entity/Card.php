<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Card
 *
 * @ORM\Table(name="card")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\CardRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Card extends AbstractBaseEntity
{

    public $skillDelimiter = "|";

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

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $race;

    public function __toString()
    {
        try {
            $str = $this->getName();
            $str .= " ";
            $str .= $this->getDelay();
            $str .= "/";
            $str .= $this->getAttack();
            $str .= "/";
            $str .= $this->getDefense();
            $str .= " ";

            foreach ($this->getSkills() as $skill) {
                $str .= $skill . " ";
            }
            $str .= " (".Card::getFactionName($this->getRace()).")";
        } catch (\Exception $ex) {
            echo $ex->getMessage() . "\n";
            echo $ex->getTraceAsString();
            $str = "";
        }

        return $str;
    }

    public function __construct()
    {
        $this->defense = 0;
        $this->delay = 0;
        $this->attack = 0;
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

    /**
     * @return int
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * @param int $race
     */
    public function setRace($race)
    {
        $this->race = $race;
    }

    public static function getFactionName($factionId)
    {
        switch ($factionId) {
            case 1:
                return "Imperial";
            case 2:
                return "Raider";
            case 3:
                return "Bloodthirsty";
            case 4:
                return "Xeno";
            case 5:
                return "Righteous";
            case 6:
                return "Progenitor";
            default:
                return $factionId;
        }
    }
}
