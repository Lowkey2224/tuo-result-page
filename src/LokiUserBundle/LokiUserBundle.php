<?php

namespace LokiUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LokiUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
