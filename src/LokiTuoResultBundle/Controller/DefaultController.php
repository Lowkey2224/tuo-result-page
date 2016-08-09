<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * @param $fileId
     * @return Response|NotFoundHttpException
     * @Route("/file/{fileId}", requirements={"fileId":"\d+"}, name="tuo.resultfile.show")
     */
    public function getFile($fileId)
    {
        $file = $this->getDoctrine()->getRepository('LokiTuoResultBundle:ResultFile')->find($fileId);
        if (is_null($file)) {
            return $this->createNotFoundException();
        }
        $filename = "result.txt";
        return new Response($file->getContent(), 200, [
            'content-type' => 'text/text',
            'cache-control' => 'private',
            'content-disposition' => 'attachment; filename="'.$filename.'";',
        ]);
    }
}
