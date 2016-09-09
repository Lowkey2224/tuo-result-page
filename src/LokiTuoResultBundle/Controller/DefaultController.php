<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Mission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="tuo.index")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $um = $this->get('loki_tuo_result.user.manager');

        $guilds = $um->getGuildsForUser($user);
        $missionRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission');

        $groupedBy = [];
        /** @var Mission $mission */
        foreach ($guilds as $guild) {
            $groupedBy[$guild] = $missionRepo->findMissionsForGuild($guild);
        }

        return $this->render(
            'LokiTuoResultBundle:Default:index.html.twig',
            [
                'missions' => $groupedBy,
            ]
        );
    }

    /**
     * @Route("/mission/{missionId}/guild/{guild}", requirements={"missionId":"\d+", "guild":"[a-zA-Z]+"}, name="tuo.showmission")
     * @param int $missionId Id of the mission
     * @param string $guild name of the guild
     * @return Response
     */
    public function showMission($missionId, $guild)
    {
        if(!in_array($guild, $this->getParameter('guilds')))
        {
            throw new NotFoundHttpException();
        }

        $userManager = $this->get('loki_tuo_result.user.manager');
        $guilds = $userManager->getGuildsForUser($this->getUser());

        if(!in_array($guild, $guilds))
        {
            throw new AccessDeniedHttpException();
        }

        $mission = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->find($missionId);
        if (!$mission) {
            throw new NotFoundHttpException();
        }
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')->findBy(['mission' => $mission, 'guild' => $guild]);
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
            'content-disposition' => 'attachment; filename="' . $filename . '";',
        ]);
    }

    public function uploadResult()
    {
        $path = "";
        $resultReader = $this->get('loki_tuo_result.reader');
        $guild = "";
        $id = $resultReader->readFile($path, $guild);
        $resultCount = $resultReader->importFileById($id, $guild);
        return $resultCount;
    }
}
