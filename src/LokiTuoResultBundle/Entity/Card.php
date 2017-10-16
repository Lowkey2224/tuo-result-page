<?php

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Card.
 *
 * @ORM\Table(name="card")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\CardRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Card extends AbstractBaseEntity
{

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=255, unique=true)
     */
    private $name;

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

    /**
     * @var CardLevel[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CardLevel", mappedBy="card", cascade={"remove", "persist"}, orphanRemoval=true, fetch="EAGER")
     */
    private $levels;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->levels = new ArrayCollection();
    }

    /**
     * Set name.
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return int
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * @return string
     */
    public function getRaceName()
    {
        return self::getFactionName($this->getRace());
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
                return 'Imperial';
            case 2:
                return 'Raider';
            case 3:
                return 'Bloodthirsty';
            case 4:
                return 'Xeno';
            case 5:
                return 'Righteous';
            case 6:
                return 'Progenitor';
            default:
                return $factionId;
        }
    }

    /**
     * Returns an ArrayCollection with Keys = tuId
     * @return ArrayCollection|CardLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param int $tuoId
     * @return CardLevel|null
     */
    public function getLevelByTuId(int $tuoId)
    {
        return $this->levels->get($tuoId);
    }

    /**
     * Returns the CardLEvel with the desired Level or null if no level exists.
     * @param int $level
     * @return CardLevel|null
     */
    public function getLevel(int $level = null)
    {
        $useMax = is_null($level);
        $return = null;
        foreach ($this->getLevels() as $elem) {
            if ($useMax) {
                $level = max($level, $elem->getLevel());
            }
            if ($elem->getLevel() === $level) {
                $return = $elem;
            }
        }

        return $return;
    }

    /**
     * @param ArrayCollection|CardLevel[] $levels
     * @return Card
     */
    public function setLevels($levels)
    {
        $this->levels = new ArrayCollection();
        foreach ($levels as $level) {
            $this->levels->set($level->getTuoId(), $level);
        }
        return $this;
    }

}
