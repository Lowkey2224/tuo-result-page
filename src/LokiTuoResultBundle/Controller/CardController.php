<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Card;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CardController.
 *
 * @Route("/card")
 */
class CardController extends Controller
{
    /**
     * @Route("/", name="loki.tuo.card.index")
     */
    public function indexAction()
    {
        $repo    = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card');
        $char    = 'A';
        $letters = [];
        $cards   = [];
        $count   = 0;
        for ($i = 0; $i < 26; ++$i) {
            $cards[$char] = $repo->findByStartingLetter($char);
            $count += count($cards[$char]);
            $letters[] = $char;
            ++$char;
        }

        return $this->render('LokiTuoResultBundle:Card:index.html.twig', [
            'cardsArray' => $cards,
            'letters'    => $letters,
            'count'      => $count,
        ]);
    }

    /**
     * @Route("/all", name="loki.tuo.cards.all")
     *
     * @return JsonResponse
     */
    public function getAllCards()
    {
        $cards = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findAll();
        $names = array_map(function (Card $card) {
            return $card->getName();
        }, $cards);

        return new JsonResponse($names);
    }
}
