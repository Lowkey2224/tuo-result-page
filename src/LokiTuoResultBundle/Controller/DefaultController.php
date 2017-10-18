<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Player;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController.
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @route("/")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('tuo.index');
    }

    /**
     * @route("/test")
     */
    public function testAction()
    {

        $connector = $this->get('loki_tuo_result.tyrant_connector');
        $ocManager = $this->get('loki_tuo_result.owned_card.manager');

        $playerRepo = $this->getDoctrine()->getRepository("LokiTuoResultBundle:Player");
        /** @var Player $player */
        $player = $playerRepo->findOneBy(['name' => "LokiMcFly"]);
        if (!$player->hasKongCredentials()) {
            return $this->json(["error" => "NO Credentials found"]);
        }
        $ocManager->removeOldOwnedCardsForPlayer($player);
        $idAmountMap = $connector->getInventoryAndDeck($player);
        $ocs = $ocManager->persistOwnedCardsByTuoId($idAmountMap, $player);
        $res = [];
        foreach ($ocs as $oc) {
            $res[$oc->getCard()->getTuoId()] = $oc->__toString();
        }
        return $this->json($res);
    }
}
