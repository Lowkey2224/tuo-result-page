<?php

namespace LokiTuoResultBundle\Service\RabbitMq;

class TuBattleConsumer extends AbstractTuApiConsumer
{
    /**
     * @inheritdoc
     */
    protected function connectorCall()
    {
        $this->connector->battleAllBattles($this->player);
    }
}
