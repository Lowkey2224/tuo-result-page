<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:37
 */

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Form\ResultFileType;
use LokiTuoResultBundle\Form\SimulationType;
use LokiTuoResultBundle\Service\Simulation\Simulation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SimulationController
 * @package LokiTuoResultBundle\Controller
 * @Route("/simulation")
 */
class SimulationController extends Controller
{

    /**
     * @Route("/create", name="loki.tuo.sim.create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createSimulationAction(Request $request)
    {


        $sim = new Simulation();
        $options = [
            'guilds' => $this->getParameter('guilds'),
        ];
        $form = $this->createForm(SimulationType::class, $sim, $options);
        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if (!$form->isValid()) {
                return $this->render('LokiTuoResultBundle:Simulation:index.html.twig', array(
                    'form' => $form->createView(),
                ));
            }

            $res = $this->get('loki_tuo_result.simulation.manager')->getSimulation($sim);
//            echo $res;
            $filename = $sim->getScriptType() == "shell" ?"mass_sim.sh":"mass_sim.bat";
            return new Response($res, 200, [
                'content-type' => 'text/text',
                'cache-control' => 'private',
                'content-disposition' => 'attachment; filename="' . $filename . '";',
            ]);
        }
        return $this->render('LokiTuoResultBundle:Simulation:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

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
     * @Route("/mission/{missionId}", requirements={"missionId":"\d+"}, name="tuo.showmission")
     * @param int $missionId Id of the mission
     * @return Response
     * @Security("has_role('ROLE_USER')")
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
     * @Security("has_role('ROLE_USER')")
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
     * @Security("has_role('ROLE_USER')")
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
     * @Route("/upload/form", name="loki.tuo.result.upload.form")
     * @Security("has_role('ROLE_USER')")
     */
    public function uploadFormAction()
    {
        $form = $this->getUploadForm();
        return $this->render('LokiTuoResultBundle:partials:UploadModal.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getUploadForm()
    {
        return $this->createForm(ResultFileType::class, null, [
            'action' => $this->generateUrl('loki.tuo.result.upload'),
            'method' => 'POST',
        ]);
    }
}
