<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:44.
 */

namespace App\LokiTuoResultBundle\Service\Persister;

use Psr\Log\LoggerAwareTrait;

class NullPersister implements PersisterInterface
{
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function persistObjects(array $entities, array $identifiers)
    {
        return count($entities);
    }
}
