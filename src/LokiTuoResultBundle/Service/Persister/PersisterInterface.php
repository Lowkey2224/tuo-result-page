<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:53.
 */

namespace LokiTuoResultBundle\Service\Persister;

use Psr\Log\LoggerInterface;

interface PersisterInterface
{
    public function setLogger(LoggerInterface $logger);

    /**
     * Persists the Given array of AbstractBaseEntity into the according DataStorage.
     *
     * @param \LokiTuoResultBundle\Entity\AbstractBaseEntity[] $entities
     * @param $identifiers string[]
     *
     * @return int
     */
    public function persistObjects(array $entities, array $identifiers);
}
