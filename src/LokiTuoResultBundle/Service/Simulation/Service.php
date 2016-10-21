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

class Service
{

    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function getSimulation(Simulation $simulation)
    {
        $service = $simulation->getScriptType() == "shell" ? new ShellService() : new BatchService();
        $service->setLogger($this->logger);
        return $service->getSimulation($simulation);
    }


}
