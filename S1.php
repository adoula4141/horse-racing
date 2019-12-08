<?php


include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);
$inputFile = __DIR__ . "/data/racecard/$raceDate.php";
$jockeyNamesAllRaces = include($inputFile);

$totalRaces = count($jockeyNamesAllRaces);

/**
    @todo: TO LIMIT PLAY METHOD TO QPL S1 AND WIN BASED ON IN INTERSECTION OF TRIO1 OF PREVIOUS SIMILR DAYS AS CALCULATED IN SIMILITUDES.PHP
*/

$outputFile = "data/bets/$raceDate"."SetS1.php";

function getdata($raceDate, $totalRaces, $outputFile)
{
    $betting = "<?php\n\n";
    $betting .= "return [\n";

    $list = getSelection($raceDate, $totalRaces);
    $toWin = array_slice($list, 0, 2);
    $toTrio = array_slice($list, 1, 5);

    $listR2 = [];
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'k');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR2)) $listR2[] = $horses[3];
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];

    $selection = array_values(array_unique(array_merge($toWin, $listR2)));
    $dList = [];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'k');   
    if(isset($horses[0]) && !in_array($horses[0], $dList)) $dList[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $dList)) $dList[] = $horses[3];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $dList)) $dList[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $dList)) $dList[] = $horses[3];

    $iSet = array_intersect($selection, $dList);
    $dSet = array_diff($dList, $selection);
    $mSet = array_diff($selection, $iSet);
    $selection = array_values(array_unique(array_merge($mSet, $dSet)));
    $selection = array_slice($selection, 0, 3);

    $toWin = array_slice($selection, 0, 2);
    $toPlace = $selection;
    sort($toPlace);

    //add to trios.php
    $triosArrayFile = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "trios.php";
    $triosArray = include($triosArrayFile);
    if(count($toPlace) >= 3 && !in_array($toPlace, $triosArray)) $triosArray[] = $toPlace;
    $writeToTrios = "<?php\n\nreturn [\n";
    foreach ($triosArray as $trioValueArray) {
        $writeToTrios .= "\t[" . implode(", ", $trioValueArray) . "],\n";
    }
    $writeToTrios .= "];\n";
    file_put_contents($triosArrayFile, $writeToTrios);

    $unitWinBet = 100;
    $unitPlaBet = 100;
    $unitQplBet = 10;
    $unitQinBet = 10;

    if(count($toPlace) >= 3) $toTrio1 = $toPlace;
    else $toTrio1 = [];

    $toTrio2 = $toTrio;
    asort($toTrio2);

    $toTce = array_values(array_unique(array_merge($toTrio1, $toTrio2)));
    asort($toTce);
    $first4 = $toTce;

    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(!raceExists($raceDate, $raceNumber)) continue;

        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'TRIO 1' => [" . implode(", ", $toTrio1) ."],\n";
        $betting .= "\t\t'TRIO 2' => [" . implode(", ", $toTrio2) ."],\n";
        $betting .= "\t\t'TIERCE' => [" . implode(", ", $toTce) ."],\n";
        $betting .= "\t\t'FIRST 4' => [" . implode(", ", $first4) ."],\n";
        
        $betting .= "\t\t'unitWinBet' => $unitWinBet,\n";
        $betting .= "\t\t'unitPlaBet' => $unitPlaBet,\n";
        $betting .= "\t\t'unitQplBet' => $unitQplBet,\n";
        $betting .= "\t\t'unitQinBet' => $unitQinBet,\n";
        $betting .= "\t],\n";
    }

    $betting .= "];\n";

    file_put_contents($outputFile, $betting);
}

getdata($raceDate, $totalRaces, $outputFile);
