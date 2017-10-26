<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:37.
 */

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Mission;
use LokiTuoResultBundle\Entity\ResultFile;
use LokiTuoResultBundle\Form\Type\MissionType;
use LokiTuoResultBundle\Form\Type\ResultFileType;
use LokiTuoResultBundle\Form\Type\SimulationType;
use LokiTuoResultBundle\Service\Simulation\Simulation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SimulationController.
 *
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
        $sim  = new Simulation();
        $form = $this->createForm(SimulationType::class, $sim);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $res      = $this->get('loki_tuo_result.simulation.manager')->getSimulation($sim);
            $filename = $sim->getScriptType() == 'shell' ? 'mass_sim.sh' : 'mass_sim.bat';

            return new Response($res, 200, [
                'content-type'        => 'text/text',
                'cache-control'       => 'private',
                'content-disposition' => 'attachment; filename="'.$filename.'";',
            ]);
        }

        return $this->render('LokiTuoResultBundle:Simulation:index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create/vpc", name="loki.tuo.sim.create.vpc")
     * @Security("has_role('ROLE_USER')")
     */
    public function createVpcAction(Request $request)
    {
        $sim  = new Simulation();
        $form = $this->createForm(SimulationType::class, $sim);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $res = $this->get('loki_tuo_result.vpc_simulation.manager')->post2($sim);
            if ($res['id']) {
                $msg = 'Simulation with Id <a href="http://tuo.throwingbones.com/job/%d">%d</a> has been created';
                $this->addFlash('success', sprintf($msg, $res['id'], $res['id']));
            }
        }

        return $this->render('LokiTuoResultBundle:Simulation:index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="tuo.index")
     */
    public function indexAction()
    {
        $missionRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Mission');
        $groupedBy   = $missionRepo->findAllWithGuilds2();

        return $this->render(
            'LokiTuoResultBundle:Default:index.html.twig',
            [
                'missions' => $groupedBy,
            ]
        );
    }

    /**
     * @Route("/edit/{id}",name="loki.tuo.mission.edit", methods={"GET","POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Mission $mission
     *
     * @return Response
     */
    public function editMissionAction(Request $request, Mission $mission)
    {
        $options = [];
        $form    = $this->createForm(MissionType::class, $mission, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($mission);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tuo.index');
        }

        return $this->render(
            'LokiTuoResultBundle:Simulation:editMission.html.twig',
            [
                'mission' => $mission,
                'form'    => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}",name="loki.tuo.mission.delete")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Mission $mission
     *
     * @return Response
     */
    public function deleteMissionAction(Mission $mission)
    {
        $this->getDoctrine()->getManager()->remove($mission);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('tuo.index');
    }

    /**
     * @Route("/mission/{id}", requirements={"missionId":"\d+"}, name="tuo.showmission")
     *
     * @param Mission $mission
     *
     * @return Response
     * @Security("has_role('ROLE_USER')")
     *
     * @Cache(lastModified="mission.getUpdatedAt()", ETag="'Mission' ~ mission.getId() ~ mission.getUpdatedAt().getTimestamp()")
     */
    public function showMissionAction(Mission $mission)
    {
        $orderBy = ['result.guild' => 'ASC', 'result.id' => 'ASC'];
        $results = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Result')
            ->findResultsWithPlayerAndDecks($mission, $orderBy);

        return $this->render(
            'LokiTuoResultBundle:Default:showMission.html.twig',
            [
                'mission' => $mission,
                'results' => $results,
            ]
        );
    }

    /**
     * @param ResultFile $file
     *
     * @return Response
     * @Route("/file/{id}", requirements={"fileId":"\d+"}, name="tuo.resultfile.show")
     * @Security("has_role('ROLE_USER')")
     */
    public function getFileAction(ResultFile $file)
    {
        $filename = $file->getOriginalName();


        return new Response($file->getContent(), 200, [
            'content-type'        => 'text/text',
            'cache-control'       => 'private',
            'content-disposition' => 'attachment; filename="'.$filename.'";',
        ]);
    }

    /**
     * @Route("/upload", name="loki.tuo.result.upload")
     * @Security("has_role('ROLE_USER')")
     */
    public function uploadResultAction(Request $request)
    {
        $form         = $this->createForm(ResultFileType::class, null);
        $resultReader = $this->get('loki_tuo_result.reader');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['file'] instanceof UploadedFile) {
                $t           = $data['file']->getClientOriginalName();
                $id          = $resultReader->readFile($data['file']->getRealPath(), $t);
                $resultCount = $resultReader->importFileById($id);
                $this->addFlash('success', "$resultCount Results have been imported");

                return $this->redirectToRoute('tuo.index');
            } else {
                $this->addFlash('error', 'There was an error importing Resultfile');
            }
        }

        return $this->render('@LokiTuoResult/Simulation/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
