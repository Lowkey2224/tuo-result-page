<?php

namespace LokiTuoResultBundle\Service\PlayerManager;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Service
{
    use LoggerAwareTrait;
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->setLogger(new NullLogger());
    }

    public function addDefaultCard(Player $player)
    {
        if (! $player->getOwnedCards()->count()) {
            $malikaCriteria = ['name' => 'Malika'];
            $malika         = $this->em->getRepository('LokiTuoResultBundle:Card')->findOneBy($malikaCriteria);
            $this->addCardToPlayer($player, $malika, 1, 1);

            $this->em->persist($player);

            $this->em->flush();
        }
    }

    /**
     * Search for the Player or create it if he doesnt exist.
     *
     * @param Player $player
     *
     * @return Player
     */
    public function findOrCreatePlayer(Player $player)
    {
        $repo      = $this->em->getRepository('LokiTuoResultBundle:Player');
        $playerOld = $repo->findOneBy(['name' => $player->getName()]);
        if ($playerOld) {
            $playerOld->setCurrentGuild($player->getCurrentGuild());
            $playerOld->setActive(true);
            $player = $playerOld;
        }

        return $player;
    }

    /**
     * Add a Card to the Player.
     *
     * @param Player   $player
     * @param Card     $card
     * @param int      $amount
     * @param int      $amountInDeck
     * @param int|null $level
     *
     * @return OwnedCard|null|object
     */
    public function addCardToPlayer(Player $player, Card $card, int $amount, int $amountInDeck, int $level = null)
    {
        $criteria = ['card' => $card, 'player' => $player, 'level' => $level];
        $oc       = $this->em->getRepository('LokiTuoResultBundle:OwnedCard')->findOneBy($criteria);
        if ($oc) {
            $amount += $oc->getAmount();
        } else {
            $oc = new OwnedCard();
        }
        $oc->setPlayer($player);
        $oc->setCard($card);
        $oc->setAmount($amount);
        $oc->setAmountInDeck($amountInDeck);
        $oc->setLevel($level);
        $this->em->persist($oc);

        return $oc;
    }
}
