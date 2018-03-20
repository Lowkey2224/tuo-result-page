<?php


namespace App\LokiTuoResultBundle\Model;


use App\LokiTuoResultBundle\Entity\Player;

interface Message
{
    /**
     * @return Player
     */
    public function getPlayer();

    public function translateParams(): array;
}