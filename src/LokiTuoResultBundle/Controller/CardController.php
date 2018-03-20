<?php

namespace App\LokiTuoResultBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
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
        $names = new ArrayCollection($repo->findAllNames());
        for ($i = 0; $i < 26; ++$i) {
            $cards[$char] = $names->filter(function ($c) use ($char) {
                return strpos($c['name'], $char) === 0;
            });
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
    public function getAllCardsAction()
    {
        $cards = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Card')->findAllNames();

        $names = [];
        foreach ($cards as $card) {
            $names[] = $card['name'];
        }

        return new JsonResponse($names);
    }
}
