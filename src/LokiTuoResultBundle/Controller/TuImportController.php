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
     * @Route("/{id}/test.{_format}",
     *     name="loki.tuo.tui.test.player",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function testAction(Player $player)
    {
        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $options = [];
//        $options = ['battle_id' => 1509730796781, 'skip' => 1, 'card_uid' => (int)rand(1, 3)];
        $this->get('monolog.logger.tu_api')->info(print_r($options, true));
        $data = $connector->test($player, "useDailyBonus", $options);

        return $this->json($data);
    }

    /**
     * @Route("/{id}/battle.{_format}",
     *     name="loki.tuo.tui.player.battle",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function battleAction(Player $player)
    {
        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $data = $connector->battleAllBattles($player);

        return $this->json($data);
    }

    /**
     * @Route("/{id}/claim-bonus.{_format}",
     *     name="loki.tuo.tui.player.claim_card",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function claimBonusAction(Player $player)
    {
        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $data = $connector->claimBonus($player);
        if (!$data['result']) {
            $diff = \DateTime::createFromFormat("U", $data['daily_time']);
            $now = new \DateTime();

            $diff = $diff->diff($now);
            /** @var \DateInterval $diff */
            $data['daily_time'] = $diff->format("%H:%i");
        }

        return $this->json($data);
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


    /*
     getHuntingTargets => hunting_targets user_data->stamina
    huntingtarget->userId->hunting_elo Order By, then pick first
    startHuntingBattle mit body target_user_id als userId von huntingtarget. FETCH battle_data->battle_id
    playCard $options = ['battle_id' => 1509727689219, 'skip' => 1, 'card_uid' => (int)rand(1,3)]; => Rewards 0 rating change & gold

     init/getHuntingTargets => active_brawl_data, player_brawl_data
     getBattleResults => battle_data->card_map
        is_attacker_card = lambda x: 1 <= x <= 50
        is_attacker_fort = lambda x: 51 <= x < 100
        is_defender_card = lambda x: 101 <= x <= 150
        is_defender_fort = lambda x: 151 <= x < 200
        is_attacker_both = lambda x: is_attacker_card(x) or is_attacker_fort(x)
        is_defender_both = lambda x: is_defender_card(x) or is_defender_fort(x)
    useDailyBonus => result_message: [
                "Your daily bonus is not yet ready."
                ],
                daily_bonus_time: "1509733331",
            //ASUMPTION ODER ['bonus_result']['bonus']['card']
     */


}
