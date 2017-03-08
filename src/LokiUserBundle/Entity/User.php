<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 15.11.16
 * Time: 14:19
 */

namespace LokiUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="LokiTuoResultBundle\Entity\Player", mappedBy="owner")
     */
    protected $players;
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $registrationCode;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @return string
     */
    public function getRegistrationCode()
    {
        return $this->registrationCode;
    }

    /**
     * @param string $registrationCode
     */
    public function setRegistrationCode($registrationCode)
    {
        $this->registrationCode = $registrationCode;
    }

    /**
     * @return mixed
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param mixed $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
    }


}
