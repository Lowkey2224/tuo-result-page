<?php

namespace LokiTuoResultBundle\Controller;


use LokiTuoResultBundle\Entity\Player;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->get('loki_tuo_result.tu_api.update.producer')->updatePlayerInventories($player);
        $this->addFlash("success", "Update Inventory Request queued");
        return $this->json(true);
    }

    /**
     * @Route("/{id}/queue.{_format}",
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
    public function queueAction(Player $player)
    {
        $this->get('loki_tuo_result.tu_api.update.producer')->updatePlayerInventories($player);
        $this->addFlash("success", "Update Inventory Request queued");
        return $this->redirectToRoute("loki.tuo.ownedcard.cards.show", ['id' => $player->getId()]);
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
        $queueManager = $this->get('loki_tuo_result.tu_api.update.producer');
        $players = $this->getDoctrine()->getRepository("LokiTuoResultBundle:Player")->findAllWithCredentials();
        /** @var Player $player */
        foreach ($players as $player) {
            $queueManager->updatePlayerInventories($player);
        }
        $this->addFlash("success", "Update Inventory Request queued");
        return $this->redirectToRoute("loki.tuo.player.all.show");
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
        $this->get('loki_tuo_result.tu_api.battle.producer')->battleAllBatles($player);
        $this->addFlash("success", "Auto Battle Request queued");

        return $this->redirectToRoute("loki.tuo.ownedcard.cards.show", ['id' => $player->getId()]);
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


}
