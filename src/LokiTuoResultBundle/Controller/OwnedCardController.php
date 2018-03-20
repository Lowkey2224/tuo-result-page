<?php
/**
 * Created by PhpStorm.
 * User: marcu
 * Date: 05.08.2017
 * Time: 11:32
 */

namespace App\LokiTuoResultBundle\Controller;


use App\LokiTuoResultBundle\Entity\OwnedCard;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Form\Type\MassOwnedCardType;
use App\LokiTuoResultBundle\Form\Type\OwnedCardType;
use App\LokiTuoResultBundle\Service\OwnedCards\Service as OwnedCardManager;
use App\LokiTuoResultBundle\Service\PlayerManager\Service as PlayerManager;
use App\LokiTuoResultBundle\Service\TyrantApiConnector\Service as ApiConnector;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class PlayerController.
 *
 * @Route("/ownedcard")
 */
class OwnedCardController extends Controller
{

    /**
     * @Route("/id/card/deck/{id}",
     *     name="loki.tuo.ownedcard.deck.add",
     *     requirements={"id":"\d+"},
     *     methods={"POST"})
     *
     *
     * @param OwnedCard $oc
     *
     * @return JsonResponse
     */
    public function addCardToDeckAction(OwnedCard $oc)
    {
        $this->denyAccessUnlessGranted('edit.player', $oc->getPlayer());
        $commander = 1;
        $domination = 1;
        $cardSlots = 10;
        $maxCardCount = $commander + $domination + $cardSlots;
        $ownedCardRepo = $this->getDoctrine()->getRepository("LokiTuoResultBundle:OwnedCard");
        $count = $ownedCardRepo->countCardsInDeckForPlayer($oc->getPlayer());
        if ($count >= $maxCardCount) {
            return $this->json(["message" => "Too Many Cards in Deck"], 420);
        }

        if ($oc->getAmountInDeck() < $oc->getAmount()) {
            $oc->setAmountInDeck($oc->getAmountInDeck() + 1);
        }
        $oc->getPlayer()->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($oc->getPlayer());
        $this->getDoctrine()->getManager()->persist($oc);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'name' => $oc->getCard()->getName(),
            'level' => $oc->getCard()->getLevel(),
            'amount' => $oc->getAmountInDeck(),
            'id' => $oc->getId(),
        ]);
    }

    /**
     * @Route("/id/card/deck/{id}/remove",
     *     name="loki.tuo.ownedcard.deck.remove",
     *     requirements={"id":"\d+"},
     *     methods={"DELETE"})
     *
     *
     * @param OwnedCard $oc
     *
     * @return JsonResponse
     */
    public function removeCardFromDeckAction(OwnedCard $oc)
    {
        $this->denyAccessUnlessGranted('edit.player', $oc->getPlayer());
        $oc->setAmountInDeck($oc->getAmountInDeck() - 1);
        $oc->getPlayer()->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($oc->getPlayer());
        $this->getDoctrine()->getManager()->persist($oc);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'name' => $oc->getCard()->getName(),
            'level' => $oc->getCard()->getLevel(),
            'amount' => $oc->getAmountInDeck(),
            'id' => $oc->getId(),
        ]);
    }

    /**
     * @Route("/{id}/card", name="loki.tuo.ownedcard.card.add", requirements={"id":"\d+"},
     *     methods={"PUT"})
     *
     * @param OwnedCard $oc
     *
     * @return JsonResponse
     */
    public function addOwnedCardAction(OwnedCard $oc)
    {
        $this->denyAccessUnlessGranted('edit.player', $oc->getPlayer());
        $oc->setAmount($oc->getAmount() + 1);
        $oc->getPlayer()->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($oc->getPlayer());
        $this->getDoctrine()->getManager()->persist($oc);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'name' => $oc->getCard()->getName(),
            'level' => $oc->getCard()->getLevel(),
            'amount' => $oc->getAmount(),
            'id' => $oc->getId(),
        ]);
    }

    /**
     * @Route("/{id}/card/reduce",
     *     name="loki.tuo.ownedcard.card.remove",
     *     requirements={"id":"\d+"},
     *     methods={"DELETE"}
     *     )
     *
     * @param OwnedCard $oc
     *
     * @return JsonResponse
     */
    public function reduceCardAction(OwnedCard $oc)
    {
        $this->denyAccessUnlessGranted('edit.player', $oc->getPlayer());
        $player = $oc->getPlayer();
        if ($oc->getAmount() == 1) {
            $this->getDoctrine()->getManager()->remove($oc);
            $oc->setAmount(0);
        } else {
            $oc->setAmount($oc->getAmount() - 1);
            if ($oc->getAmount() < $oc->getAmountInDeck()) {
                $oc->setAmountInDeck($oc->getAmount());
            }
            $this->getDoctrine()->getManager()->persist($oc);
        }

        $player->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($player);

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse($oc->toArray());
    }

    /**
     * @Route("/{id}/cards/add",
     *     name="loki.tuo.ownedcard.card.add.mass",
     *     requirements={"id":"\d+"},
     *     methods={"PUT"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Request $request
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addMassCardsForPlayerAction(
        Request $request,
        Player $player,
        OwnedCardManager $manager,
        LoggerInterface $logger
    ) {
        $form = $this->createForm(MassOwnedCardType::class, null, [
            'action' => $this->generateUrl('loki.tuo.ownedcard.card.add.mass', ['id' => $player->getId()]),
            'method' => 'PUT',
        ]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $names = $form->getData();
            $names = $names['cards'];

            /** @var LoggerInterface $logger */
            $manager->setLogger($logger);
            $cards = [];
            foreach (explode("\n", $names) as $line) {
                $cards[] = $manager->transformCardString($line);
            }
            $cardModels = $manager->transformArrayToModels($player, $cards);
            $manager->persistOwnedCards($cardModels);

            $player->setUpdatedAtValue();
            $this->getDoctrine()->getManager()->persist($player);
        }

        return $this->redirectToRoute('loki.tuo.ownedcard.cards.show', ['id' => $player->getId()]);
    }

    /**
     * @Route("/{id}/cards/delete",
     *     name="loki.tuo.ownedcard.card.delete.mass",
     *     methods={"DELETE"},
     *     requirements={"id":"\d+"}
     *     )
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Security("is_granted('delete.player', player)")
     */
    public function deleteMassCardsForPlayerAction(
        Player $player,
        OwnedCardManager $manager,
        LoggerInterface $logger,
        PlayerManager $playerManager
    ) {
        $manager->setLogger($logger);
        $manager->removeOldOwnedCardsForPlayer($player);
        $playerManager->addDefaultCard($player);

        $player->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($player);

        return $this->redirectToRoute('loki.tuo.ownedcard.cards.show', ['id' => $player->getId()]);
    }

    /**
     * @Route("/card/create/{id}",
     *     name="loki.tuo.ownedcard.create",
     *     methods={"POST"},
     *     requirements={"id":"\d+"})
     * @Security("is_granted('edit.player', player)")
     * @param Request $request
     * @param Player $player
     * @return JsonResponse
     */
    public function createOwnedCardAction(Request $request, Player $player, PlayerManager $manager)
    {
        $name = trim($request->get('owned_card_card'));
        $level = $request->get('owned_card_level');
        $level = (is_null($level) || $level == 'null' || trim($level) == '' || 0) ? null : $level;
        $amount = $request->get('owned_card_amount');
        $card = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findOneBy(['name' => $name]);
        if (!$card || !$card->getLevel($level)) {
            return new JsonResponse(['message' => 'Card not found'], 420);
        }
        $oc = $manager->addCardToPlayer($player, $card->getLevel($level), $amount, 0);
        $player->setUpdatedAtValue();
        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse($oc->toArray());
    }

    /**
     * @param Player $player
     * @Route("/{id}/cards", name="loki.tuo.ownedcard.cards.show", requirements={"id":"\d+"})
     *
     * @ParamConverter("player", class="LokiTuoResultBundle:Player", options={"repository_method" = "findWithOwnedCards"})
     * @return Response
     * @Security("is_granted('view.player', player)")
     */
    public function showCardsForPlayerAction(Player $player, ApiConnector $connector)
    {
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
        $ownedCardForm = $this->createForm(OwnedCardType::class, null, ['attr' => ['class' => 'data-remote']]);

        $massOwnedCardForm = $this->createForm(MassOwnedCardType::class, null, [
            'action' => $this->generateUrl('loki.tuo.ownedcard.card.add.mass', ['id' => $player->getId()]),
            'method' => 'PUT',
        ]);
        $info = $player->hasKongCredentials() ? $connector->getPlayerInfo($player) : null;

        return $this->render('LokiTuoResultBundle:OwnedCard:show_cards_for_player.html.twig', [
            'canEdit' => true,
            'player' => $player,
            'deck' => $deck,
            'deckName' => $combined,
            'cards' => $allCards,
            'form' => $ownedCardForm->createView(),
            'massForm' => $massOwnedCardForm->createView(),
            'info' => $info,
        ]);
    }

}