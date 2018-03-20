<?php

namespace App\LokiTuoResultBundle\Controller;


use App\LokiTuoResultBundle\Entity\CardLevel;
use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Service\RabbitMq\TuApiProducer;
use App\LokiTuoResultBundle\Service\TyrantApiConnector\Connector;
use App\LokiTuoResultBundle\Service\TyrantApiConnector\Service as ApiConnector;
use Psr\Log\LoggerInterface;
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
    public function updateInventoryAction(Player $player, TuApiProducer $producer)
    {
        $producer->updatePlayerInventories($player, $this->getUser());
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
    public function updateAllInventoriesAction(TuApiProducer $producer)
    {
        $players = $this->getDoctrine()->getRepository("LokiTuoResultBundle:Player")->findAllWithCredentials();
        /** @var Player $player */
        foreach ($players as $player) {
            $producer->updatePlayerInventories($player, $this->getUser());
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
    public function testAction(Player $player, ApiConnector $connector, LoggerInterface $logger)
    {
        $options = [];
        $logger->info(print_r($options, true));
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
    public function battleAction(Player $player, TuApiProducer $producer)
    {
        $producer->battleAllBatles($player, $this->getUser());
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
     * @return array
     */
    public function staminaAction(Player $player, ApiConnector $connector)
    {

        if (!$player->hasKongCredentials()) {
            throw new ConflictHttpException("Has no Credentials");
        }
        return $connector->getStaminaInfo($player);
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
    public function claimBonusAction(Player $player, ApiConnector $connector)
    {
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
