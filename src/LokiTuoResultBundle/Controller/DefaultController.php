<?php

namespace App\LokiTuoResultBundle\Controller;

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
}
