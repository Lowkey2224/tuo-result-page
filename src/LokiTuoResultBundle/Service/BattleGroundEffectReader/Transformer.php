<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 05.12.16
 * Time: 20:39
 */

namespace LokiTuoResultBundle\Service\BattleGroundEffectReader;

use LokiTuoResultBundle\Entity\BattleGroundEffect;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Transformer
{
    use LoggerAwareTrait;

    public function __construct($logger = null)
    {
        $logger = null === $logger ? new NullLogger() : $logger;
        $this->setLogger($logger);
    }


    /**
     * Transforms the Array Content of a bges.txt File into Models
     * @param array $fileContent
     * @return BattleGroundEffect[]
     */
    public function transformToModels(array $fileContent)
    {
        $bges = [];
        $category = "default";
        foreach ($fileContent as $lineNumber => $line) {
            //There are 3 Types of Lines
            if ("" == $line) {
                //Empty Lines indicate a Category ends.
                $category = "default";
            } elseif (($pos = strpos($line, "//")) !== false) {
                //2nd Options is a Line starting with // which indicates the Name of a Category
                $category = trim(substr($line, $pos + 2));
            } elseif (strpos($line, ":") !== false) {
                list($name, $description) = explode(":", $line);
                //Lines that contain a BGE are marked as NAME:DESCRIPTION
                $bge = new BattleGroundEffect();
                $bge->setCategory($category);
                $bge->setName($name);
                $bge->setDescription($description);
                $bges[] = $bge;
            } else {
                $this->logger->warning("Line $lineNumber could not be recognized\n" . $line);
            }
        }
        return $bges;
    }
}
