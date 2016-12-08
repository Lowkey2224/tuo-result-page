<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package LokiTuoResultBundle\Controller
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
