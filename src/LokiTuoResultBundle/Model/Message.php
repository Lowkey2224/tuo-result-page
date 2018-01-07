<?php


namespace LokiTuoResultBundle\Model;


use LokiTuoResultBundle\Entity\Player;

interface Message
{
    /**
     * @return Player
     */
    public function getPlayer();

    public function translateParams(): array;
}