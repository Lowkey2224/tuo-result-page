<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 18.11.16
 * Time: 11:58
 */

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ImportController
 * @package LokiTuoResultBundle\Controller
 * @Route("/Import")
 */
class ImportController extends Controller
{
    /**
     * @Route("/", methods={"GET"}, name="loki.tuo.import.index")
     */
    public function indexAction()
    {
        $resultFileRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:ResultFile');
        $results = $resultFileRepo->findBy([], ['id'=>'ASC']);
        return $this->render(
            'LokiTuoResultBundle:Import:index.html.twig',
            [
                'resultFiles' => $results,
            ]
        );
    }
}
