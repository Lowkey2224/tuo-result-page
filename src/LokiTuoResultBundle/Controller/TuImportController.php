<?php

namespace LokiTuoResultBundle\Controller;


use LokiTuoResultBundle\Entity\CardLevel;
use LokiTuoResultBundle\Entity\Player;
use LokiTuoResultBundle\Service\TyrantApiConnector\Connector;
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateInventoryAction(Player $player)
    {
        $this->get('loki_tuo_result.tu_api.update.producer')->updatePlayerInventories($player, $this->getUser());
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
    public function updateAllInventoriesAction()
    {
        $queueManager = $this->get('loki_tuo_result.tu_api.update.producer');
        $players = $this->getDoctrine()->getRepository("LokiTuoResultBundle:Player")->findAllWithCredentials();
        /** @var Player $player */
        foreach ($players as $player) {
            $queueManager->updatePlayerInventories($player, $this->getUser());
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
        $data = $connector->test($player, Connector::GET_INVENTORY, $options);

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
        $this->get('loki_tuo_result.tu_api.battle.producer')->battleAllBatles($player, $this->getUser());
        $this->addFlash("success", "Auto Battle Request queued");

        return $this->redirectToRoute("loki.tuo.ownedcard.cards.show", ['id' => $player->getId()]);
    }


    /**
     * @Route("/{id}/stamina.{_format}",
     *     name="loki.tuo.tui.player.stamina",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('edit.player', player)")
     *
     * @param Player $player
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function staminaAction(Player $player)
    {

        if (!$player->hasKongCredentials()) {
            throw new ConflictHttpException("Has no Credentials");
        }
        return $this->get('loki_tuo_result.tyrant_connector')->getStaminaInfo($player);
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
            $this->addFlash("info", sprintf("Next Bonus available in %s", $data['daily_time']));
        } else {
            /** @var CardLevel $level */
            $level = $this->getDoctrine()->getRepository('LokiTuoResultBundle:CardLevel')->findOneBy(['tuoId' => $data['tuId']]);
            $this->addFlash("success", sprintf("Got Card %s", $level->getCard()->getName()));
        }

        return $this->redirectToRoute("loki.tuo.ownedcard.cards.show", ['id' => $player->getId()]);
    }


}
