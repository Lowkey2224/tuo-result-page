<?php

namespace LokiTuoResultBundle\Controller;

use Illuminate\Support\Collection;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Form\MassOwnedCardType;
use LokiTuoResultBundle\Form\OwnedCardType;
use LokiTuoResultBundle\Form\PlayerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PlayerController
 * @package LokiTuoResultBundle\Controller
 * @Route("/player")
 */
class PlayerController extends Controller
{

    /**
     * @Route("/{playerId}/results", name="loki.tuo.player.results.show", requirements={"playerId":"\d+"})
     */
    public function showResultsForPlayerAction($playerId)
    {

        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return $this->createNotFoundException("Player not Found");
        }
        if (!$this->get('loki.user.user.manager')->canUserAccess($this->getUser(), $player->getGuild())) {
            throw new AccessDeniedHttpException();
        }

        $results = $player->getResults();

        return $this->render('LokiTuoResultBundle:Player:showResultsForPlayer.html.twig', array(
            'player' => $player,
            'results' => $results
        ));
    }

    /**
     * @Route("/", name="loki.tuo.player.all.show", methods={"GET"})
     */
    public function listAllPlayersAction()
    {

        $players = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->getPlayerWithLastUpdatedDate(false);

        $form = $this->getPlayerForm();
        return $this->render('LokiTuoResultBundle:Player:listAllPlayers.html.twig', [
            'players' => $players,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @param $playerId
     * @return Response
     * @Route("/{playerId}/inventory", requirements={"playerId":"\d+"}, name="loki.tuo.player.inventory.show")
     * @throws NotFoundHttpException
     */
    public function getFileAction($playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (is_null($player)) {
            throw $this->createNotFoundException("Player with this ID not found");
        }
        $content = "";
        /** @var OwnedCard $ownedCard */
        foreach($player->getOwnedCards() as $ownedCard)
        {
            $content.=$ownedCard."\n";
        }
        $filename = "ownedcards.txt";
        return new Response($content, 200, [
            'content-type' => 'text/text',
            'cache-control' => 'private',
            'content-disposition' => 'attachment; filename="' . $filename . '";',
        ]);
    }

    /**
     * @Route("/playerId/card/deck/{playerId}",
     *     name="loki.tuo.player.card.deck.add",
     *     methods={"POST"},
     *     requirements={"playerId":"\d+"})
     * @param $playerId
     * @return JsonResponse
     */
    public function addCardToDeckAction(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $name = $request->get('owned_card_card');
        $level = $request->get('owned_card_level') == "null" ? null : $request->get('owned_card_level');
        $amount = $request->get('owned_card_amount');
        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found'], 420);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');
        $oc = $ownedCardRepo->findOneBy(['player' => $player, 'card' => $card, 'level' => $level]);
        if (!$oc) {
            return new JsonResponse(['message' => 'Card ' . $card->getName() . ' not found for Player'], 420);
        }
        $count = $ownedCardRepo->countCardsInDeckForPlayer($player);
        //If there are more than 1 Cards in the Dack we cant add more cards
        if ($amount > 0) {
            if ($count > 10) {
                return new JsonResponse(['message' => "Can't add more cards to Deck for player."], 420);
            } elseif ((10 - $count) > $amount) {
                $amount = 10 - $count;
            }
        }

        if ($oc->getAmount() < $oc->getAmountInDeck() + $amount) {
            $oc->setAmountInDeck($oc->getAmount());
        } else {
            $oc->setAmountInDeck($oc->getAmountInDeck() + $amount);
        }
        $this->getDoctrine()->getEntityManager()->persist($oc);
        $this->getDoctrine()->getEntityManager()->flush();
        return new JsonResponse([
            'name' => $name,
            'level' => $level,
            'amount' => $oc->getAmountInDeck(),
            'id' => $oc->getId()
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
        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
//        var_dump($name, $card);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found'], 420);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');
        $oc = $ownedCardRepo->findOneBy(['player' => $player, 'card' => $card]);
        if (!$oc) {
            $oc = new OwnedCard();
            $oc->setPlayer($player);
            $oc->setCard($card);
            $oc->setAmount(0);
        }

        $level = (is_null($level) || $level == "null" || trim($level) == "") ? null : $level;
        $oc->setLevel($level);
        $oc->setAmount($oc->getAmount() + $amount);
        $this->getDoctrine()->getEntityManager()->persist($oc);
        $this->getDoctrine()->getEntityManager()->flush();
        return new JsonResponse([
            'name' => $name,
            'level' => $level,
            'amount' => $oc->getAmount(),
            'id' => $oc->getId()
        ]);
    }

    /**
     * @Route("/{playerId}/card/reduce",
     *     name="loki.tuo.player.card.remove",
     *     methods={"DELETE"},
     *     requirements={"playerId":"\d+"}
     *     )
     * @param Request $request
     * @param $playerId
     * @return JsonResponse
     */
    public function reduceCardAction(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $name = $request->get('owned_card_card');
        $level = $request->get('owned_card_level') == "null" ? null : $request->get('owned_card_level');

        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found'], 420);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');
        $criteria = [
            'player' => $player,
            'card' => $card,
            'level' => $level
        ];

        $oc = $ownedCardRepo->findOneBy($criteria);
        if (!$oc) {
            return new JsonResponse(['message' => 'Card ' . $card->getName() . ' not found for Player'], 420);
        }
        $amt = $oc->getAmount();
        $id = $oc->getId();
        if ($amt == 1) {
            $this->getDoctrine()->getEntityManager()->remove($oc);
        } else {
            $oc->setAmount($amt - 1);
            if ($oc->getAmountInDeck() > $oc->getAmount()) {
                $oc->setAmountInDeck($oc->getAmount());
            }
        }

        $this->getDoctrine()->getEntityManager()->flush();

        return new JsonResponse(['name' => $name, 'level' => $level, 'amount' => $amt - 1, 'id' => $id]);
    }

    /**
     * @Route("/{playerId}/cards",
     *     name="loki.tuo.player.card.add.mass",
     *     methods={"POST"},
     *     requirements={"playerId":"\d+"}
     *     )
     * @param Request $request
     * @param $playerId
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addMassCardsForPlayer(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }

        $form = $this->createForm(MassOwnedCardType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $names = $form->getData();
            $names = $names['cards'];

            $manager = $this->get('loki_tuo_result.owned_card.manager');
            $manager->setLogger($this->get('logger'));
            $cards = [];
            foreach (explode("\n", $names) as $line) {
                $cards[] = $manager->transformCardString($line);
            }
            $cardModels = $manager->transformArrayToModels($player, $cards);
            $manager->persistOwnedCards($cardModels);
        }


        return $this->redirectToRoute('loki.tuo.player.cards.show', ['playerId' => $playerId]);
    }

    /**
     * @Route("/{playerId}/cards", name="loki.tuo.player.cards.show", requirements={"playerId":"\d+"})
     */
    public function showCardsForPlayerAction($playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return $this->createNotFoundException("Player not Found");
        }
        if (!$this->get('loki.user.user.manager')->canUserAccess($this->getUser(), $player->getGuild())) {
            throw new AccessDeniedHttpException();
        }


        $allCards = $player->getOwnedCards();
        $allCards = Collection::make($allCards)->sortBy(function (OwnedCard $elem) {
            return $elem->getCard()->getName();
        });
        $deck = $allCards->filter(function (OwnedCard $item) {
            return $item->getAmountInDeck() > 0;
        });
        $formOptions = ['attr' => ['class' => 'data-remote']];
        $ownedCardForm = $this->createForm(OwnedCardType::class, null, $formOptions);

        $massOwnedCardForm = $this->createForm(MassOwnedCardType::class, null, array(
            'action' => $this->generateUrl('loki.tuo.player.card.add.mass', ['playerId' => $playerId]),
            'method' => 'POST',
        ));

        return $this->render('LokiTuoResultBundle:Player:show_cards_for_player.html.twig', array(
            'canEdit' => true,
            'player' => $player,
            'deck' => $deck,
            'cards' => $allCards,
            'form' => $ownedCardForm->createView(),
            'massForm' => $massOwnedCardForm->createView(),
        ));
    }

    /**
     * @Route("/{playerId}/disable", name="loki.tuo.player.disable", requirements={"playerId":"\d+"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_MODERATOR')")
     * @param $playerId
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disablePlayerAction($playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $player->setActive(false);
        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();
//        var_dump($player);die();
        return $this->redirectToRoute('loki.tuo.player.all.show');
    }


    /**
     * @param Request $request
     * @param $playerId
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/{playerId}/edit", name="loki.tuo.player.edit", requirements={"playerId":"\d+"})
     */
    public function editPlayer(Request $request, $playerId)
    {
        $player = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->find($playerId);
        if (!$player) {
            return new JsonResponse(['message' => 'Player not found', 404]);
        }
        $action = $this->generateUrl('loki.tuo.player.edit', ['playerId' => $playerId]);
        $form = $this->getPlayerForm($player, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('loki.tuo.player.all.show');
        }


        return $this->render(
            '@LokiTuoResult/Player/edit.html.twig',
            [
                'player' => $player,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/", name="loki.tuo.player.add", methods={"POST"})
     */
    public function addPlayerAction(Request $request)
    {

        $player = new Player();
        $form = $this->getPlayerForm($player);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Check if Player already exists

            // Add a single Card
            $malikaCriteria = ['name' => "Malika"];
            $malika = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy($malikaCriteria);
            $oc = new OwnedCard();
            $oc->setPlayer($player);
            $oc->setCard($malika);
            $oc->setAmount(1);
            $oc->setAmountInDeck(1);
            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->persist($oc);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('loki.tuo.player.cards.show', ['playerId' => $player->getId()]);
        } else {
            var_dump($form->isSubmitted(), $form->isValid());
//            die();
            return $this->redirectToRoute('loki.tuo.player.all.show');
        }


    }

    private function getPlayerForm(Player $player = null, $action = null)
    {
        if ($action === null) {
            $action = $this->generateUrl("loki.tuo.player.add");
        }
        return $this->createForm(PlayerType::class, $player, [
            'action' => $action,
            'method' => 'POST',
            'guilds' => $this->getParameter('guilds'),
        ]);
    }
}
