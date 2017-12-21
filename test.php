<?php
$msg = [
    "Fought 10 battles, won 5, Won 203 gold and 1000 rating",
//    "Fought 10 battles, won 5, Won 203 gold and 1000 rating",
//    "Fought 10 battles, won 5, Won 203 gold and 1000 rating",
//    "Fought 10 battles, won 5, Won 203 gold and 1000 rating",
];
foreach ($msg as $m) {
    $matches = [];
    preg_match_all('/[a-zA-Z]+ (\d+)/', $m, $matches);
    var_dump($matches);
    $f = $matches[1][0];
    $w = $matches[1][1];
    $g = $matches[1][2];
    $r = $matches[1][3];
}