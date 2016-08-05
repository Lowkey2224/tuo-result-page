<?php

namespace LokiTuoResultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $missions = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->findAll();
        return $this->render('LokiTuoResultBundle:Default:index.html.twig',
            [
                'missions' => $missions,
            ]);
    }

    /**
     * @Route("/mission/{missionId}", requirements={"missionId":"\d+"}, name="tuo.showmission")
     */
    public function showMission($missionId)
    {
        $mission = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->find($missionId);
        if(!$mission){
            return $this->createNotFoundException();
        }
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')->findBy(['mission'=>$mission]);
        return $this->render('LokiTuoResultBundle:Default:showMission.html.twig',
            [
                'mission' => $mission,
                'results' => $results,
            ]);
    }


}
