<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class QueueItemController
 * @package LokiTuoResultBundle\Controller
 * @Route("/queue")
 */
class QueueItemController extends Controller
{
    /**
     * @Route("/", name="loki.tuo.queue.index")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $repo = $this->getDoctrine()->getRepository("LokiTuoResultBundle:QueueItem");
        $items = $repo->findActive();
        return ['items' => $items];

    }
}
