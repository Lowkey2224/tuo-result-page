<?php

namespace LokiTuoResultBundle\Controller;

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
        $player = $playerRepo->findOneByName("LokiMcFly");
        $ids = $connector->getDecks();
        $ocs = $ocManager->persistOwnedCardsByTuoId($ids, $player);
        $summe = array_sum($ids);
        $diff = array_diff(array_keys($ids), array_keys($ocs));

        return $this->json(['sumOfCards' => $summe, 'diff' => $diff, 'fetched' => $ids]);
    }
}
