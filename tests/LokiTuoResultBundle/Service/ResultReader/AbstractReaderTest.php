<?php

namespace LokiTuoResultBundle\Service\ResultReader;

use Doctrine\ORM\EntityManager;
use LokiTuoResultBundle\Service\OwnedCards\Service as CardManager;
use LokiTuoResultBundle\Service\Reader\JsonImporter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;

class AbstractReaderTest extends TestCase
{
    /**
     * @param $line
     * @param $expected
     * @dataProvider lineProvider
     */
    public function testParseResultLine($line, $expected)
    {
        $em = $this->createMock(EntityManager::class);
        $cm = $this->createMock(CardManager::class);
        $service = new JsonImporter($em, $cm, new NullLogger());
        $method = $this->getMethod(JsonImporter::class, "parseResultLine");
        $result = $method->invoke($service, $line);
        $this->assertEquals($expected, $result);
    }

    public function lineProvider()
    {
        return [
            ["Optimized Deck: 6 units: 80.87: Malika, Stonewall Garrison",
                ['simType' => 'Mission', 'percent' => 808, 'deck' => ['Malika', 'Stonewall Garrison']]],
            ["Optimized Deck: 6 units: 80: Malika, Stonewall Garrison",
                ['simType' => 'Mission', 'percent' => 800, 'deck' => ['Malika', 'Stonewall Garrison']]],
            ["Optimized Deck: 6 units: 80.8: Malika, Stonewall Garrison",
                ['simType' => 'Mission', 'percent' => 808, 'deck' => ['Malika', 'Stonewall Garrison']]],
            ["Optimized Deck: 6 units: 100: Malika, Stonewall Garrison",
                ['simType' => 'Mission', 'percent' => 1000, 'deck' => ['Malika', 'Stonewall Garrison']]],
        ];
    }


    /**
     * Returns a Method on which you can use invokeMethod($obj, [$params])
     * @param string $className name of the Class
     * @param string $methodName methodname
     * @return \ReflectionMethod
     */
    protected function getMethod($className, $methodName)
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
