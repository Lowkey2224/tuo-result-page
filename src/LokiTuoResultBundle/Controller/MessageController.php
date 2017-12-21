<?php

namespace LokiTuoResultBundle\Controller;


use LokiTuoResultBundle\Entity\BattleLog;
use LokiTuoResultBundle\Entity\Message;
use LokiTuoResultBundle\Entity\Player;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MessageController
 * @package LokiTuoResultBundle\Controller
 * @Route("/message")
 */
class MessageController extends Controller
{
    /**
     * @param Player $player
     * @Route("/count/player/{id}.{_format}",
     *     name="loki.tuo.message.count.player",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format": "json"}
     *     )
     * @Security("is_granted('delete.player', player)")
     * @return JsonResponse
     */
    public function countMessageAction(Player $player)
    {
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:BattleLog');
        $msg = $repo->findUnreadByPlayer($player);
        return $this->json(["count" => count($msg) . ""]);
    }

    /**
     * @Route("/count/user.{_format}",
     *     name="loki.tuo.message.count.user",
     *     defaults={"_format": "json"},
     *     requirements={"_format": "json"}
     *     )
     * @return JsonResponse
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countUserMessageAction()
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_REMEMBERED");
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:BattleLog');
        $totalCount = $repo->countForUser($this->getUser());
        return $this->json(["count" => $totalCount]);
    }

    /**
     * @param Player $player
     * @return array
     * @Route("/{id}.{_format}",
     *     name="loki.tuo.message.show",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format":"json"}
     *     )
     * @Security("is_granted('delete.player', player)")
     * @Template()
     */
    public function showAction(Player $player)
    {
        $repo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:BattleLog');
        $msg = $repo->findUnreadByPlayer($player);
        $total = $repo->getTotaldByPlayer($player);
        return ["messages" => $msg, "total" => $total];
    }

    /**
     * @param Message $message
     * @return JsonResponse
     * @Route("/{id}/read.{_format}",
     *     name="loki.tuo.message.read",
     *     defaults={"_format": "json"},
     *     requirements={"id":"\d+", "_format":"json"}
     *     )
     * @Security("is_granted('view.message', message)")
     */
    public function markReadAction(BattleLog $message)
    {
        $message->setStatusRead();
        $this->getDoctrine()->getManager()->persist($message);
        $this->getDoctrine()->getManager()->flush();
        return $this->json($message->serialize());
    }

}
