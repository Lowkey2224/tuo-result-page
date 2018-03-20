<?php

namespace App\LokiTuoResultBundle\Controller;

use App\LokiTuoResultBundle\Entity\KongregateCredentials;
use App\LokiTuoResultBundle\Entity\OwnedCard;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Form\Type\PlayerType;
use App\LokiTuoResultBundle\Security\PlayerVoter;
use App\LokiTuoResultBundle\Service\PlayerManager\Service as PlayerManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PlayerController.
 *
 * @Route("/player")
 */
class PlayerController extends Controller
{
    /**
     * @Route("/{id}/results", name="loki.tuo.player.results.show", requirements={"id":"\d+"})
     * @Security("is_granted('view.player', player)")
     *
     * @param Player $player
     *
     * @return Response
     */
    public function showResultsForPlayerAction(Player $player)
    {
        return $this->render('LokiTuoResultBundle:Player:showResultsForPlayer.html.twig', [
            'player' => $player,
            'results' => $player->getResults(),
        ]);
    }

    /**
     * @Route("/", name="loki.tuo.player.all.show", methods={"GET"})
     *
     * @return Response
     */
    public function listAllPlayersAction()
    {
        $criteria = $this->isGranted('ROLE_ADMIN') ? [] : ['active' => true];

        $players = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Player')->findBy($criteria);

        $form = $this->getPlayerForm();

        return $this->render('LokiTuoResultBundle:Player:listAllPlayers.html.twig', [
            'players' => $players,
            'form' => $form->createView(),
            'showUpdateButton' => $this->isGranted('ROLE_MODERATOR')
        ]);
    }

    /**
     * @param Player $player
     *
     * @return RedirectResponse
     * @Route("/{id}/claim/confirm", requirements={"id":"\d+"}, name="loki.tuo.player.claim.confirm")
     * @Security("has_role('ROLE_MODERATOR')")
     */
    public function conformClaimAction(Player $player)
    {
        if (!$player->isOwnershipConfirmed() && $player->getOwner()) {
            $player->setOwnershipConfirmed(true);
            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('loki.tuo.player.all.show'));
    }

    /**
     * @param Player $player
     * @Route("/{id}/inventory", requirements={"id":"\d+"}, name="loki.tuo.player.inventory.show")
     * @Security("is_granted('view.player', player)")
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function getFileAction(Player $player)
    {
        $content = '';
        /** @var OwnedCard $ownedCard */
        foreach ($player->getOwnedCards() as $ownedCard) {
            $content .= $ownedCard . "\n";
        }
        $filename = 'ownedcards.txt';

        return new Response($content, 200, [
            'content-type' => 'text/text',
            'cache-control' => 'private',
            'content-disposition' => 'attachment; filename="' . $filename . '";',
        ]);
    }

    /**
     * @Route("/{id}/disable", name="loki.tuo.player.disable", requirements={"id":"\d+"})
     * @Security("is_granted('delete.player', player)")
     *
     * @param Player $player
     *
     * @return RedirectResponse
     */
    public function disablePlayerAction(Player $player)
    {
        $player->setActive(false);
        $this->getDoctrine()->getManager()->persist($player);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('loki.tuo.player.all.show');
    }

    /**
     * @param Request $request
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/{id}/edit", name="loki.tuo.player.edit", requirements={"id":"\d+"})
     */
    public function editPlayerAction(Request $request, Player $player)
    {
        $action = $this->generateUrl('loki.tuo.player.edit', ['id' => $player->getId()]);
        if (!$player->hasKongCredentials()) {
            $player->setKongCredentials(new KongregateCredentials());
        }

        $playerForm = $this->getPlayerForm($player, $action, $this->isGranted(PlayerVoter::DELETE, $player));
        $playerForm->handleRequest($request);
        $canEdit = $this->isGranted(PlayerVoter::EDIT, $player);

        if ($playerForm->isSubmitted() && $playerForm->isValid()) {
            $this->getDoctrine()->getManager()->persist($player);
            if (!$player->getOwner()) {
                $player->setOwnershipConfirmed(false);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('loki.tuo.player.all.show');
        }

        return $this->render(
            '@LokiTuoResult/Player/edit.html.twig',
            [
                'player' => $player,
                'form' => $playerForm->createView(),
                'canEdit' => $canEdit,
            ]
        );
    }

    /**
     * @Route("/", name="loki.tuo.player.add", methods={"POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function addPlayerAction(Request $request, PlayerManager $manager)
    {
        $player = new Player();
        $form = $this->getPlayerForm($player);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $player = $manager->findOrCreatePlayer($player);

            // Check if Player already exists
            $manager->addDefaultCard($player);

            return $this->redirectToRoute('loki.tuo.ownedcard.cards.show', ['id' => $player->getId()]);
        } else {
            $this->addFlash('error', 'Form Invalid');

            return $this->redirectToRoute('loki.tuo.player.all.show');
        }
    }

    /**
     * Create a Player Form.
     *
     * @param Player|null $player
     * @param string $action
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getPlayerForm(Player $player = null, $action = null, $withCredentials = false)
    {
        if ($action === null) {
            $action = $this->generateUrl('loki.tuo.player.add');
        }

        return $this->createForm(PlayerType::class, $player, [
            'action' => $action,
            'method' => 'POST',
            'guilds' => $this->getParameter('guilds'),
            'withCredentials' => $withCredentials,
        ]);
    }
}
