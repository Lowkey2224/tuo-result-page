<?php

namespace LokiTuoResultBundle\Model;

class PlayerInfo
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getStamina()
    {
        return $this->data->user_data->stamina;
    }

    public function getEnergy()
    {
        return $this->data->user_data->energy;
    }

    public function getMaxEnergy()
    {
        return $this->data->max_energy;
    }

    public function getMaxStamina()
    {
        return $this->data->max_stamina;
    }

    public function getBonusCardReadyAt()
    {
        $date = $this->data->daily_bonus_time;
        return \DateTime::createFromFormat("U", $date);
    }

    public function isCardReady()
    {
        return $this->getBonusCardReadyAt() < new \DateTime();
    }
}
