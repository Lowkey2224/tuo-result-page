<?php

namespace LokiTuoResultBundle\Controller;


use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Entity\QueueItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class PlayerController.
 *
 * @Route("/tui")
 */
class TuImportController extends Controller
{

    /**
     * @Route("/{id}/update.{_format}",
     *     name="loki.tuo.tui.update.player",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateInventoryAction(Player $player)
    {
        $queue  = $this->getQueueItem();
        $this->setQueueRunning($queue);

        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $ocManager = $this->get('loki_tuo_result.owned_card.manager');

        /** @var Player $player */
        if (!$player->hasKongCredentials()) {
            $msg = $this->get('translator')->trans("ownedcard.player.update.no_credentials");
            $this->freeQueue($queue);
            throw new ConflictHttpException($msg, null, 420);
        }
        $idAmountMap = $connector->getInventoryAndDeck($player);
        if(empty($idAmountMap)) {
            $this->get("logger")->debug("No Cards will be changed via import");
            $ocs = [];
            $this->addFlash('error', sprintf("Whoops looks like something went wrong. No Cards could be fetched for you", count($ocs)));
        } else {
            $this->get("logger")->debug("Cards fetched. Import Begins now.");
            $ocManager->removeOldOwnedCardsForPlayer($player);
            $ocs = $ocManager->persistOwnedCardsByTuoId($idAmountMap, $player);
            $this->addFlash('success', sprintf("Added %d Cards", count($ocs)));
        }

        $res = [];

        foreach ($ocs as $oc) {
            $res[$oc->getCard()->getTuoId()] = $oc->toArray();
        }
        $this->freeQueue($queue);
        return $this->json($res);
    }

    /**
     * @Route("/update.{_format}",
     *     name="loki.tuo.tui.update.all",
     *     defaults={"_format": "json"},
     *     requirements={"_format": "json"}
     *     )
     * @Security("has_role('ROLE_MODERATOR')")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAllInventories()
    {
        $stopwatch = $this->get('debug.stopwatch');
        $stopwatch->start('fetchAll');

        $queue  = $this->getQueueItem();
        $this->setQueueRunning($queue);
        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $ocManager = $this->get('loki_tuo_result.owned_card.manager');
        $players = $this->getDoctrine()->getRepository("LokiTuoResultBundle:Player")->findAllWithCredentials();

        $successPlayers = [];
        /** @var Player $player */
        foreach ($players as $player){
            $idAmountMap = $connector->getInventoryAndDeck($player);
            if(!empty($idAmountMap)) {
                $this->get("logger")->debug("Cards fetched. Import Begins now.");
                $ocManager->removeOldOwnedCardsForPlayer($player);
                $ocManager->persistOwnedCardsByTuoId($idAmountMap, $player);
                $successPlayers[] = $player->getName();
            }
        }
        $this->addFlash("Success", sprintf("Players: %s were updated", implode(", ", $successPlayers)));
        $this->freeQueue($queue);
        $e = $stopwatch->stop("fetchAll");

        $this->get('monolog.logger.tu_api')->info(sprintf("Duration %d ms for %d players", $e->getDuration(),
            count($players)));
        return $this->json(["result" => true]);
    }

    /**
     * @return QueueItem
     */
    private function getQueueItem()
    {
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:QueueItem');
        $item = $repo->findOneBy(['name' => 'tuImport']);
        if(!$item) {
            $item = new QueueItem();
            $item->setName('tuImport')
                ->setUpdatedBy($this->getUser())
                ->setStatusWaiting();
        }
        return $item;
    }

    private function setQueueRunning(QueueItem $queue)
    {
//        if($queue->isRunning() ) {
//            $msg = $this->get('translator')->trans('ownedcard.player.update.already_running %name%', ['%name%' => $queue->getUpdatedBy()->getUsername()]);
//            throw new ConflictHttpException($msg, null, 409);
//        }
        $em = $this->getDoctrine()->getManager();
        $queue->setStatusRunning("")->setUpdatedBy($this->getUser());
        $em->persist($queue);
        $em->flush();
    }

    private function freeQueue(QueueItem $queue)
    {
        $em = $this->getDoctrine()->getManager();
        $queue->setStatusWaiting()->setUpdatedBy($this->getUser());
        $em->persist($queue);
        $em->flush();
    }

}
