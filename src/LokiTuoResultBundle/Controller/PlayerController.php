<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Form\OwnedCardType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class PlayerController
 * @package LokiTuoResultBundle\Controller
 * @Route("/player")
 */
class PlayerController extends Controller
{
    /**
     * @Route("/{playerId}/cards", name="loki.tuo.player.cards.show", requirements={"playerId":"\d+"})
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
        $formOptions = ['attr' => ['class' => 'data-remote']];
        $form = $this->createForm(OwnedCardType::class, null, $formOptions);

        return $this->render('LokiTuoResultBundle:Player:show_cards_for_player.html.twig', array(
            'canEdit' => true,
            'player' => $player,
            'deck' => $deck,
            'cards' => $rest,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{playerId}/results", name="loki.tuo.player.results.show", requirements={"playerId":"\d+"})
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
     * @Route("/", name="loki.tuo.player.all.show")
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

    /**
     * @Route("/{playerId}/card", name="loki.tuo.player.card.add", methods={"POST"}, requirements={"playerId":"\d+"})
     * @param Request $request
     * @param $playerId
     * @return JsonResponse
     */
    public function addOwnedCardAction(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $name = $request->get('owned_card_card');
        $level = $request->get('owned_card_level');
        $amount = $request->get('owned_card_amount');
        $inDeck = $request->get('in_deck');
        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found', 420]);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');
        $oc = $ownedCardRepo->findOneBy(['player' => $player, 'card' => $card]);
        if (!$oc) {
            $oc = new OwnedCard();
            $oc->setPlayer($player);
            $oc->setCard($card);
        }
        $level = (trim($level) == "") ? null : $level;
        $oc->setLevel($level);
        $oc->setAmount($amount);
        $oc->setInCurrentDeck($inDeck);
//        $this->getDoctrine()->getEntityManager()->persist($oc);
//        $this->getDoctrine()->getEntityManager()->flush();
        return new JsonResponse(['name' => $name, 'level' => $level, 'amount' => $amount]);
    }

    /**
     * @Route("/{playerId}/card",
     *     name="loki.tuo.player.card.remove",
     *     methods={"DELETE"},
     *     requirements={"playerId":"\d+"}
     *     )
     * @param Request $request
     * @param $playerId
     * @return JsonResponse
     */
    public function removeCardAction(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $name = $request->get('owned_card_card');
        $level = $request->get('owned_card_level') == "null" ? null : $request->get('owned_card_level');
        $amount = $request->get('owned_card_amount');
        $inDeck = $request->get('in_deck');

        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found', 420]);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');
        $criteria = [
            'player' => $player,
            'card' => $card,
            'level' => $level,
            'amount' => $amount,
            'inCurrentDeck' => $inDeck
        ];

        $oc = $ownedCardRepo->findOneBy($criteria);
        if (!$oc) {
            return new JsonResponse(['message' => 'Card not found', 420]);
        }

//        $this->getDoctrine()->getEntityManager()->remove($oc);
//        $this->getDoctrine()->getEntityManager()->flush();
        return new JsonResponse(['name' => $name, 'level' => $level, 'amount' => $amount]);
    }
}
