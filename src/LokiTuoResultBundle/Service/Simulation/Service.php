<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 30.08.16
 * Time: 08:56
 */

namespace LokiTuoResultBundle\Service\Simulation;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Service
{

    use LoggerAwareTrait;

    private $engine;

    public function __construct(EngineInterface $templating)
    {
        $this->logger = new NullLogger();
        $this->engine = $templating;
    }

    public function getSimulation(Simulation $simulation)
    {
        $service = $simulation->getScriptType() == "shell" ? new ShellService($this->engine) : new BatchService();
        $service->setLogger($this->logger);
        return $service->getSimulation($simulation);
    }
}
