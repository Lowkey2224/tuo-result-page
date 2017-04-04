<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:12.
 */

namespace LokiTuoResultBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BatteGroundEffect
 * This is a Property which can be used in Simulations.
 *
 * @ORM\Table(name="battle_ground_effect")
 * @ORM\Entity(repositoryClass="LokiTuoResultBundle\Repository\BattleGroundEffectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BattleGroundEffect extends AbstractBaseEntity
{
    /**
     * @var string Name of the Battleground Effect
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @var string The description of the Effect
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @var string The Category of the Effect e.g. "Conquest Zones"
     * @ORM\Column(type="string")
     */
    private $category;

    /**
     * @var Mission[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Mission", mappedBy="bge", cascade={"remove"})
     */
    private $missions;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection|Mission[]
     */
    public function getMissions()
    {
        return $this->missions;
    }

    /**
     * @param ArrayCollection|Mission[] $missions
     */
    public function setMissions($missions)
    {
        $this->missions = $missions;
    }
}
