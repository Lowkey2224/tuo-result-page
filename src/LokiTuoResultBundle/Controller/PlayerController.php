<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        if (!$this->get('loki_tuo_result.user.manager')->canUserAccess($this->getUser(), $player->getGuild())) {
            throw new AccessDeniedHttpException();
        }

        $allCards = $player->getOwnedCards();
        $deck = $allCards->filter(function (OwnedCard $item) {
            return $item->isInCurrentDeck();
        });
        $rest = $allCards->filter(function (OwnedCard $item) {
            return !$item->isInCurrentDeck();
        });

        return $this->render('LokiTuoResultBundle:Player:show_cards_for_player.html.twig', array(
            'player' => $player,
            'deck' => $deck,
            'cards' => $rest,
        ));
    }

    /**
     * @Route("/{playerId}/results", name="loki.tuo.player.results.show")
     */
    public function showResultsForPlayerAction($playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);

        if (!$this->get('loki_tuo_result.user.manager')->canUserAccess($this->getUser(), $player->getGuild())) {
            throw new AccessDeniedHttpException();
        }

        $results = $player->getResults();

        return $this->render('LokiTuoResultBundle:Player:showResultsForPlayer.html.twig', array(
            'player' => $player,
            'results' => $results
        ));
    }

    /**
     * @Route("/all", name="loki.tuo.player.all.show")
     */
    public function listAllPlayersAction()
    {
        $players = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->findAll();
        $userManager = $this->get('loki_tuo_result.user.manager');
        $user = $this->getUser();
        $players = array_filter($players, function (Player $player) use ($user, $userManager) {
            return $userManager->canUserAccess($user, $player->getGuild());
        });
        return $this->render('LokiTuoResultBundle:Player:listAllPlayers.html.twig', [
            'players' => $players,
        ]);
    }
}
