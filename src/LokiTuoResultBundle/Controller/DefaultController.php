<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="tuo.index")
     */
    public function indexAction()
    {
        $missions = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->findAll();
        return $this->render(
            'LokiTuoResultBundle:Default:index.html.twig',
            [
                'missions' => $missions,
            ]
        );
    }

    /**
     * @Route("/mission/{missionId}", requirements={"missionId":"\d+"}, name="tuo.showmission")
     */
    public function showMission($missionId)
    {
        $mission = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->find($missionId);
        if (!$mission) {
            return $this->createNotFoundException();
        }
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')->findBy(['mission'=>$mission]);
        return $this->render(
            'LokiTuoResultBundle:Default:showMission.html.twig',
            [
                'mission' => $mission,
                'results' => $results,
            ]
        );
    }


}
