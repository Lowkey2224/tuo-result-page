<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class CardController
 * @package LokiTuoResultBundle\Controller
 * @Route("/card")
 */
class CardController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card');
        $char = "A";
        $letters = [];
        $cards = [];
        $count = 0;
        for ($i = 0; $i < 26; $i++) {
            $cards[$char] = $repo->findByStartingLetter($char);
            $count += count($cards[$char]);
            $letters[] = $char;
            $char++;
        }


        return $this->render('LokiTuoResultBundle:Card:index.html.twig', array(
            'cardsArray' => $cards,
            'letters' => $letters,
            'count' => $count
        ));
    }
}
