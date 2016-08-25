<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class PlayerController
 * @package LokiTuoResultBundle\Controller
 * @Route("/player")
 */
class PlayerController extends Controller
{
    /**
     * @Route("/{playerId}/cards", name="loki.tuo.player.cards.show")
     */
    public function showCardsForPlayerAction($playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);


        return $this->render('LokiTuoResultBundle:Player:show_cards_for_player.html.twig', array(
            'player' => $player,
            'cards' => $player->getOwnedCards()
        ));
    }

}
