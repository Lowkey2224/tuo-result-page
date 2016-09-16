<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Form\ResultFileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
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

        $form = $this->getUploadForm();

        $missionRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission');

        $groupedBy = $missionRepo->findAll();


        return $this->render(
            'LokiTuoResultBundle:Default:index.html.twig',
            [
                'missions' => $groupedBy,
                'user' => $user,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/mission/{missionId}/guild/{guild}", requirements={"missionId":"\d+",
     *     "guild":"[a-zA-Z]+"}, name="tuo.showmission.guild")
     * @param int $missionId Id of the mission
     * @param string $guild name of the guild
     * @return Response
     */
    public function showMissionForGuildAction($missionId, $guild)
    {
        if (!in_array($guild, $this->getParameter('guilds'))) {
            throw new NotFoundHttpException();
        }

        $userManager = $this->get('loki_tuo_result.user.manager');
        $guilds = $userManager->getGuildsForUser($this->getUser());

        if (!in_array($guild, $guilds)) {
            throw new AccessDeniedHttpException();
        }

        $mission = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->find($missionId);
        if (!$mission) {
            throw new NotFoundHttpException();
        }
        $criteria = ['mission' => $mission, 'guild' => $guild];
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')->findBy($criteria);
        return $this->render(
            'LokiTuoResultBundle:Default:showMission.html.twig',
            [
                'mission' => $mission,
                'results' => $results,
            ]
        );
    }

    /**
     * @Route("/mission/{missionId}", requirements={"missionId":"\d+"}, name="tuo.showmission")
     * @param int $missionId Id of the mission
     * @return Response
     */
    public function showMissionAction($missionId)
    {
        $mission = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission')->find($missionId);
        if (!$mission) {
            throw new NotFoundHttpException();
        }
        $criteria = ['mission' => $mission];
        $orderBy = ['guild' => 'ASC', 'id' => 'ASC'];
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')->findBy($criteria, $orderBy);
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
     * @return Response
     * @Route("/file/{fileId}", requirements={"fileId":"\d+"}, name="tuo.resultfile.show")
     * @throws NotFoundHttpException
     */
    public function getFileAction($fileId)
    {
        $file = $this->getDoctrine()->getRepository('LokiTuoResultBundle:ResultFile')->find($fileId);
        if (is_null($file)) {
            throw $this->createNotFoundException("File with this ID not found");
        }
        $filename = "result.txt";
        return new Response($file->getContent(), 200, [
            'content-type' => 'text/text',
            'cache-control' => 'private',
            'content-disposition' => 'attachment; filename="' . $filename . '";',
        ]);
    }

    /**
     * @return int
     * @Route("/upload", name="loki.tuo.result.upload", methods={"POST"})
     */
    public function uploadResultAction(Request $request)
    {

        $form = $this->getUploadForm();
        $resultReader = $this->get('loki_tuo_result.reader');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['file'] instanceof UploadedFile) {
                $id = $resultReader->readFile($data['file']->getRealPath());
                $resultCount = $resultReader->importFileById($id);
                $this->addFlash('success', "$resultCount Results have been imported");
            } else {
                $this->addFlash('error', "There was an error importing Resultfile");
            }
        }
        return $this->redirectToRoute('tuo.index');
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getUploadForm()
    {
        return $this->createForm(ResultFileType::class, null,[
            'action' => $this->generateUrl('loki.tuo.result.upload'),
            'method' => 'POST',
        ]);
    }
}
