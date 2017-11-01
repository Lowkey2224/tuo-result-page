<?php

namespace LokiTuoResultBundle\Unit\Service;

use PHPUnit\Framework\TestCase;

abstract class AbstractServiceTest extends TestCase
{
    protected function getFilePath()
    {
        return __DIR__ . '/../../files/';
    }
}
