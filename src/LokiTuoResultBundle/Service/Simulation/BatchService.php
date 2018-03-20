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
use Symfony\Component\Templating\EngineInterface;

class BatchService implements SimulationCreatorInterface
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
        $result = $this->engine->render('@LokiTuoResult/Simulation/batch_script.twig', [
            'simulation' => $simulation,
        ]);

        return $result;
    }
}
