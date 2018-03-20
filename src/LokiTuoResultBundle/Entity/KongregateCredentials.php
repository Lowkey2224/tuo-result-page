<?php

namespace App\LokiTuoResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class KongregateCredentials
 * @package App\LokiTuoResultBundle\Entity
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\LokiTuoResultBundle\Repository\KongregateCredentialsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class KongregateCredentials extends AbstractBaseEntity
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongPassword;

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tuUserId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $synCode;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongUserName;

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kongId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $kongToken;

    /**
     * @return string
     */
    public function getKongPassword()
    {
        return $this->kongPassword;
    }

    /**
     * @param string $kongPassword
     * @return KongregateCredentials
     */
    public function setKongPassword(string $kongPassword)
    {
        $this->kongPassword = $kongPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getTuUserId()
    {
        return $this->tuUserId;
    }

    /**
     * @param string $tuUserId
     * @return KongregateCredentials
     */
    public function setTuUserId(string $tuUserId)
    {
        $this->tuUserId = $tuUserId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSynCode()
    {
        return $this->synCode;
    }

    /**
     * @param string $synCode
     * @return KongregateCredentials
     */
    public function setSynCode(string $synCode)
    {
        $this->synCode = $synCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getKongUserName()
    {
        return $this->kongUserName;
    }

    /**
     * @param string $kongUserName
     * @return KongregateCredentials
     */
    public function setKongUserName(string $kongUserName)
    {
        $this->kongUserName = $kongUserName;
        return $this;
    }

    /**
     * @return string
     */
    public function getKongId()
    {
        return $this->kongId;
    }

    /**
     * @param string $kongId
     * @return KongregateCredentials
     */
    public function setKongId(string $kongId)
    {
        $this->kongId = $kongId;
        return $this;
    }

    /**
     * @return string
     */
    public function getKongToken()
    {
        return $this->kongToken;
    }

    /**
     * @param string $kongToken
     * @return KongregateCredentials
     */
    public function setKongToken(string $kongToken)
    {
        $this->kongToken = $kongToken;
        return $this;
    }

    public function isValid()
    {
        return $this->kongToken
            && $this->kongId
            && $this->kongPassword
            && $this->tuUserId
            && $this->synCode
            && $this->kongUserName;

    }


}
