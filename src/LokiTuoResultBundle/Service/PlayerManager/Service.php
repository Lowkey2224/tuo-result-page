<?php

namespace LokiTuoResultBundle\Service\PlayerManager;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\CardLevel;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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
        if (!$player->getOwnedCards()->count()) {
            $malikaCriteria = ['name' => 'Malika'];
            /** @var Card $malika */
            $malika         = $this->em->getRepository('LokiTuoResultBundle:Card')->findOneBy($malikaCriteria);


            $this->addCardToPlayer($player, $malika->getLevels()->first(), 1, 1);

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
            $playerOld->setGuild($player->getGuild());
            $playerOld->setActive(true);
            $player = $playerOld;
        }

        return $player;
    }

    /**
     * Add a Card to the Player.
     *
     * @param Player   $player
     * @param CardLevel     $card
     * @param int      $amount
     * @param int      $amountInDeck
     *
     * @return OwnedCard|null
     */
    public function addCardToPlayer(Player $player, CardLevel $card, int $amount, int $amountInDeck)
    {
        $oc       = $this->em->getRepository('LokiTuoResultBundle:OwnedCard')->findOneBy(['card' => $card]);
        if ($oc) {
            $amount += $oc->getAmount();
            $amountInDeck += $oc->getAmountInDeck();
        } else {
            if(! $card instanceof  CardLevel){
                return null;
            }
            $oc = new OwnedCard();
            $oc->setCard($card);
            $oc->setPlayer($player);
        }

        return $this->updateOwnedCard($oc, $amount, $amountInDeck);
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
     * @return OwnedCard|null
     */
    public function reduceCardForPlayer(Player $player, Card $card, int $amount, int $amountInDeck, int $level = null)
    {
        $criteria = ['card' => $card, 'player' => $player, 'level' => $level];
        $oc       = $this->em->getRepository('LokiTuoResultBundle:OwnedCard')->findOneBy($criteria);
        if (!$oc) {
            throw new NotFoundResourceException('Owned Card was not Found');
        }
        $amount       = $oc->getAmount() - $amount;
        $amountInDeck = $oc->getAmountInDeck() - $amountInDeck;

        return $this->updateOwnedCard($oc, $amount, $amountInDeck);
    }

    /**
     * Updates the OwnedCard amount.
     *
     * @param OwnedCard $ownedCard
     * @param int       $amount
     * @param int       $amountInDeck
     *
     * @return OwnedCard|null
     */
    private function updateOwnedCard(OwnedCard $ownedCard, int $amount, int $amountInDeck)
    {
        //You can have more cards in your deck than you own
        $amountInDeck = $amountInDeck > $amount ? $amount : $amountInDeck;
        $ownedCard->setAmount($amount);
        $ownedCard->setAmountInDeck($amountInDeck);

        if ($ownedCard->getAmount() == 0) {
            $this->em->remove($ownedCard);

            return $ownedCard;
        }

        $this->em->persist($ownedCard);

        return $ownedCard;
    }
}
