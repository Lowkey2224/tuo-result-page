<?php

namespace LokiTuoResultBundle\Controller;

use Illuminate\Support\Collection;
use LokiTuoResultBundle\Entity\Card;
use LokiTuoResultBundle\Entity\OwnedCard;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Form\MassOwnedCardType;
use LokiTuoResultBundle\Form\OwnedCardType;
use LokiTuoResultBundle\Form\PlayerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/{id}/results", name="loki.tuo.player.results.show", requirements={"id":"\d+"})
     * @param Player $player
     * @return Response
     */
    public function showResultsForPlayerAction(Player $player)
    {

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
     * @return Response
     */
    public function listAllPlayersAction()
    {


        $players = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')
            ->getPlayerWithLastUpdatedDate(!$this->isGranted('ROLE_ADMIN'));

        $form = $this->getPlayerForm();
        return $this->render('LokiTuoResultBundle:Player:listAllPlayers.html.twig', [
            'players' => $players,
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * @param Player $player
     * @return RedirectResponse
     * @Route("/{id}/claim", requirements={"id":"\d+"}, name="loki.tuo.player.claim")
     */
    public function claimPlayerAction(Player $player)
    {
        $user = $this->getUser();
        // If Player exists claim player.
        if ($player && !$player->isOwnershipConfirmed()) {
            $player->setOwner($user);
            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirect($this->generateUrl('loki.tuo.player.all.show'));
    }

    /**
     *
     * @param Player $player
     * @return RedirectResponse
     * @Route("/{id}/claim/confirm", requirements={"id":"\d+"}, name="loki.tuo.player.claim.confirm")
     * @Security("has_role('ROLE_MODERATOR')")
     */
    public function conformClaimAction(Player $player)
    {
        if ($player && !$player->isOwnershipConfirmed() && $player->getOwner()) {
            $player->setOwnershipConfirmed(true);
            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirect($this->generateUrl('loki.tuo.player.all.show'));
    }

    /**
     * @param Player $player
     * @Route("/{id}/inventory", requirements={"id":"\d+"}, name="loki.tuo.player.inventory.show")
     * @return Response
     * @throws NotFoundHttpException
     */
    public function getFileAction(Player $player)
    {
        $content = "";
        /** @var OwnedCard $ownedCard */
        foreach ($player->getOwnedCards() as $ownedCard) {
            $content .= $ownedCard . "\n";
        }
        $filename = "ownedcards.txt";
        return new Response($content, 200, [
            'content-type' => 'text/text',
            'cache-control' => 'private',
            'content-disposition' => 'attachment; filename="' . $filename . '";',
        ]);
    }

    /**
     * @Route("/playerId/card/deck/{id}",
     *     name="loki.tuo.player.card.deck.add",
     *     methods={"POST"},
     *     requirements={"id":"\d+"})
     * @param Player $player
     * @return JsonResponse
     */
    public function addCardToDeckAction(Request $request, Player $player)
    {
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
        $this->getDoctrine()->getManager()->persist($oc);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse([
            'name' => $name,
            'level' => $level,
            'amount' => $oc->getAmountInDeck(),
            'id' => $oc->getId()
        ]);
    }

    /**
     * @Route("/{id}/card", name="loki.tuo.player.card.add", methods={"POST"}, requirements={"id":"\d+"})
     * @param Request $request
     * @param Player $player
     * @return JsonResponse
     */
    public function addOwnedCardAction(Request $request, Player $player)
    {
        $name = trim($request->get('owned_card_card'));
        $level = $request->get('owned_card_level');
        $level = (is_null($level) || $level == "null" || trim($level) == "") ? null : $level;
        $amount = $request->get('owned_card_amount');
        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
//        var_dump($name, $card);
        if (!$card) {
            return new JsonResponse(['message' => 'Card not found'], 420);
        }
        $ownedCardRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard');

        $oc = $ownedCardRepo->findOneBy(['player' => $player, 'card' => $card, 'level' => $level]);
        if (!$oc) {
            $oc = new OwnedCard();
            $oc->setPlayer($player);
            $oc->setCard($card);
            $oc->setAmount(0);
        }


        $oc->setLevel($level);
        $oc->setAmount($oc->getAmount() + $amount);
        $this->getDoctrine()->getManager()->persist($oc);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse([
            'name' => $name,
            'level' => $level,
            'amount' => $oc->getAmount(),
            'id' => $oc->getId()
        ]);
    }

    /**
     * @Route("/{id}/card/reduce",
     *     name="loki.tuo.player.card.remove",
     *     methods={"DELETE"},
     *     requirements={"id":"\d+"}
     *     )
     * @param Request $request
     * @param Player $player
     * @return JsonResponse
     */
    public function reduceCardAction(Request $request, Player $player)
    {
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
            $this->getDoctrine()->getManager()->remove($oc);
        } else {
            $oc->setAmount($amt - 1);
            if ($oc->getAmountInDeck() > $oc->getAmount()) {
                $oc->setAmountInDeck($oc->getAmount());
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['name' => $name, 'level' => $level, 'amount' => $amt - 1, 'id' => $id]);
    }

    /**
     * @Route("/{id}/cards",
     *     name="loki.tuo.player.card.add.mass",
     *     methods={"POST"},
     *     requirements={"id":"\d+"}
     *     )
     * @param Request $request
     * @param Player $player
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addMassCardsForPlayer(Request $request, Player $player)
    {
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


        return $this->redirectToRoute('loki.tuo.player.cards.show', ['playerId' => $player->getId()]);
    }

    /**
     * @Route("/{id}/cards/delete",
     *     name="loki.tuo.player.card.delete.mass",
     *     methods={"GET"},
     *     requirements={"playerId":"\d+"}
     *     )
     * @param Player $player
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('edit', player)")
     */
    public function deleteMassCardsForPlayer(Player $player)
    {

        if ($this->isGranted('ROLE_MODERATOR') || $player->isOwnedBy($this->getUser())) {
            $manager = $this->get('loki_tuo_result.owned_card.manager');
            $manager->setLogger($this->get('logger'));
            $manager->removeOldOwnedCardsForPlayer($player);
            $this->addDefaultCardToPlayer($player);
        }
        return $this->redirectToRoute('loki.tuo.player.cards.show', ['playerId' => $player->getId()]);
    }

    /**
     *
     * @param Player $player
     * @Route("/{id}/cards", name="loki.tuo.player.cards.show", requirements={"playerId":"\d+"})
     * @return Response
     */
    public function showCardsForPlayerAction(Player $player)
    {
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
        $combined = $deck->map(function (OwnedCard $item) {
            return $item->toDeckString();
        });
        $formOptions = ['attr' => ['class' => 'data-remote']];
        $ownedCardForm = $this->createForm(OwnedCardType::class, null, $formOptions);

        $massOwnedCardForm = $this->createForm(MassOwnedCardType::class, null, array(
            'action' => $this->generateUrl('loki.tuo.player.card.add.mass', ['id' => $player->getId()]),
            'method' => 'POST',
        ));

        return $this->render('LokiTuoResultBundle:Player:show_cards_for_player.html.twig', array(
            'canEdit' => true,
            'player' => $player,
            'deck' => $deck,
            'deckName' => $combined,
            'cards' => $allCards,
            'form' => $ownedCardForm->createView(),
            'massForm' => $massOwnedCardForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/disable", name="loki.tuo.player.disable", requirements={"playerId":"\d+"})
     * @Security("has_role( 'ROLE_MODERATOR')")
     * @param Player $player
     * @return RedirectResponse
     */
    public function disablePlayerAction(Player $player)
    {
        $player->setActive(false);
        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();
//        var_dump($player);die();
        return $this->redirectToRoute('loki.tuo.player.all.show');
    }


    /**
     * @param Request $request
     * @param Player $player
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/[player}/edit", name="loki.tuo.player.edit", requirements={"playerId":"\d+"})
     */
    public function editPlayer(Request $request, Player $player)
    {
        $action = $this->generateUrl('loki.tuo.player.edit', ['id' => $player->getId()]);
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
     * @param Request $request
     * @return RedirectResponse
     */
    public function addPlayerAction(Request $request)
    {


        $player = new Player();
        $form = $this->getPlayerForm($player);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $player = $this->findOrCreatePlayer($player);

            // Check if Player already exists
            $this->addDefaultCardToPlayer($player);
            return $this->redirectToRoute('loki.tuo.player.cards.show', ['playerId' => $player->getId()]);
        } else {
            var_dump($form->isSubmitted(), $form->isValid());
//            die();
            return $this->redirectToRoute('loki.tuo.player.all.show');
        }
    }

    /**
     *
     * @param Player $player
     */
    private function addDefaultCardToPlayer(Player $player)
    {
        if (!$player->getOwnedCards()->count()) {
            $malikaCriteria = ['name' => "Malika"];
            $malika = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy($malikaCriteria);
            $this->addCardToPlayer($player, $malika, 1, 1);

            $this->getDoctrine()->getManager()->persist($player);

            $this->getDoctrine()->getManager()->flush();
        }
    }

    private function findOrCreatePlayer(Player $player)
    {
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player');
        $playerOld = $repo->findOneBy(['name' => $player->getName()]);
        if ($playerOld) {
            $playerOld->setCurrentGuild($player->getCurrentGuild());
            $playerOld->setActive(true);
            $player = $playerOld;
        }

        return $player;
    }

    private function addCardToPlayer(Player $player, Card $card, int $amount, int $amountInDeck, int $level = null)
    {
        $criteria = ['card' => $card, 'player' => $player, 'level' => $level];
        $oc = $this->getDoctrine()->getRepository('LokiTuoResultBundle:OwnedCard')->findOneBy($criteria);
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
        $this->getDoctrine()->getManager()->persist($oc);
        return $oc;
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
