<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 21.10.16
 * Time: 15:50.
 */

namespace App\LokiTuoResultBundle\Service\Simulation;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ShellService implements SimulationCreatorInterface
{
    use LoggerAwareTrait;

    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->logger = new NullLogger();
        $this->engine = $engine;
    }

    public function getSimulation(Simulation $simulation)
    {
        $simulation->setResultFile("result.json");
        return $this->engine->render('@LokiTuoResult/Simulation/shell_script_v2.sh.twig', [
            'simulation' => $simulation,
        ]);
    }
}
