<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 17.10.16
 * Time: 15:37
 */

namespace LokiTuoResultBundle\Controller;


use LokiTuoResultBundle\Form\SimulationType;
use LokiTuoResultBundle\Service\Simulation\Simulation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SimulationController
 * @package LokiTuoResultBundle\Controller
 * @Route("/sim")
 */
class SimulationController extends Controller
{

    /**
     * @Route("/", name="loki.tuo.sim.create")
     */
    public function showResultsForPlayerAction(Request $request)
    {


        $sim = new Simulation();
        $form = $this->createForm(SimulationType::class, $sim);
        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if (!$form->isValid()) {
                return $this->render('LokiTuoResultBundle:Simulation:index.html.twig', array(
                    'form' => $form->createView(),
                ));
            }

            $res = $this->get('loki_tuo_result.simulation.manager')->getSimulation($sim);
//            echo $res;
            $filename = "mass_sim.sh";
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

}