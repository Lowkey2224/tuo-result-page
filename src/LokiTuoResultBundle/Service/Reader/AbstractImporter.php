<?php


namespace LokiTuoResultBundle\Service\Reader;


use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Result;

abstract class AbstractImporter
{
    /** @var EntityManager  */
    protected $em;

    protected function parseResultLine($line)
    {
        if (preg_match('/member name (.*?)@/', $line, $name) === 1) {
            $name                                   = $name[1];
            $result['playername'] = $name;
        }
        if (preg_match('/against (.*)/', $line, $name) === 1) {
            $name                                = $name[1];
            $result['mission'] = $name;
        }

        $result['simType'] = 'Mission';
        if (preg_match('/(\d?\d(.\d*)?):/', $line, $name) === 1) {
            $name                                = $name[1];
            $name                                = (int) ($name * 10);
            $result['percent'] = $name;
        }
        if (preg_match('/\d?\d.?\d?\d?: (.*)/', $line, $name) === 1) {
            $name                             = $name[1];
            $cards                            = $this->transformToCardNames(explode(', ', $name));
            $result['deck'] = $cards;
        }
        if (preg_match('/(\d\d?% win)/', $line) === 1) {
            $result['simType'] = 'Raid';
        }
        return $result;
    }

    /**
     * Transform raw Cardstrings into correct Cardnames.
     *
     * @param array $array
     *
     * @return array
     */
    private function transformToCardNames(array $array)
    {
        $result = [];
        foreach ($array as $name) {
            if(preg_match('/([\w\h]+)/', $name, $res)===1){
                $result[] = trim($res[0]);
            }

        }
        return $result;
    }



    /**
     * Remove old Deck for previous Results.
     *
     * @param Result $result
     */
    protected function deleteOldDeck(Result $result)
    {
        foreach ($result->getDeck() as $deckItem) {
            $this->em->remove($deckItem);
        }
        $this->em->persist($result);
    }
}