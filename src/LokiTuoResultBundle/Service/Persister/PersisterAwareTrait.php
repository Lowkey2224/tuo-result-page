<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:57.
 */

namespace App\LokiTuoResultBundle\Service\Persister;

use App\LokiTuoResultBundle\Service\Persister\Exception\NoPersisterExpection;

trait PersisterAwareTrait
{
    /** @var PersisterInterface */
    private $persister;

    public function setPersister(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @throws NoPersisterExpection If no persister is set
     *
     * @return PersisterInterface
     */
    public function getPersister()
    {
        if (!$this->persister) {
            throw new NoPersisterExpection();
        }

        return $this->persister;
    }
}
