<?php

namespace LokiTuoResultBundle\Service\Reader;


/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 03.08.16
 * Time: 19:38
 */
class Service
{
    public function readFile($path)
    {
        $content = $this->getFileContents($path);
        $transformed  = $this->transformContent($content);
        $models = $this->transformToModels($transformed);

    }

    private function getFileContents($path)
    {
        $content = [];
        $handle = fopen($path, "r");
        if ($handle) {
            $line = fgets($handle); //Throw away first line.
            while (($line = fgets($handle)) !== false) {
                $content[] = $line;
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
        return $content;
    }

    private function transformContent($content)
    {
        $result = [];
        $firstLine = true;
        $count = 0;
        foreach ($content as $line) {
            if($firstLine){
                if (preg_match('/member name (.*?)@/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['playername'] = $name;
                }
                if (preg_match('/against (.*?) with/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['mission'] = $name;
                }
                $firstLine = false;
            }else{
                if (preg_match('/units: (\d\d.\d?):/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['percent'] = $name;
                }
                if (preg_match('/units: \d\d.\d: (.*)/', $line, $name) === 1) {
                    $name = $name[1];
                    $result[$count]['deck'] = explode(", ",$name);
                }
                $firstLine = true;
            }
        }
        var_dump($result);
        return $result;
    }

    private function transformToModels($transformed)
    {
        $result = [];
        foreach ($transformed as $line)
        {

        }
    }
}