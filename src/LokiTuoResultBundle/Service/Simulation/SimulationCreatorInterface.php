<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 21.10.16
 * Time: 15:53.
 */

namespace App\LokiTuoResultBundle\Service\Simulation;

interface SimulationCreatorInterface
{
    public function getSimulation(Simulation $simulation);
}
