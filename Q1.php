<?php


include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);
$inputFile = __DIR__ . "/data/racecard/$raceDate.php";
$jockeyNamesAllRaces = include($inputFile);

$totalRaces = count($jockeyNamesAllRaces);

$outputFile = "data/bets/$raceDate"."SetQ1.php";

function getdata($raceDate, $totalRaces, $outputFile, $jockeyNamesAllRaces)
{
    $betting = "<?php\n\n";
    $betting .= "return [\n";

    $list = getSelection($raceDate, $totalRaces);
    $selection = array_slice($list, 1, 5);

    $list1 = array_slice($list, 0, 4);
    $list2 = array_slice($list, 4, 4);
    $list3 = array_slice($list, 8);

    $unitWinBet = 100;
    $unitPlaBet = 100;
    $unitQplBet = 10;
    $unitQinBet = 10;

    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(!raceExists($raceDate, $raceNumber)) continue;

        $toWin = $selection;
        $toPlace = $toWin;

        $qpl1 = [];
        if(isset($list1[1]) && !in_array($list1[1], $qpl1)) $qpl1[] = $list1[1];
        if(isset($list1[2]) && !in_array($list1[2], $qpl1)) $qpl1[] = $list1[2];
        if(isset($list2[1]) && !in_array($list2[1], $qpl1)) $qpl1[] = $list2[1];

        if(count($qpl1) < 2) $qpl1 = [];
        
        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $qpl1) . "],\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $qpl1) . "],\n";
        $betting .= "\t\t'TRIO' => [" . implode(", ", $selection) ."],\n";
        $betting .= "\t\t'TIERCE' => [" . implode(", ", $selection) ."],\n";
        $betting .= "\t\t'FIRST 4' => [" . implode(", ", $selection) ."],\n";
        
        $betting .= "\t\t'unitWinBet' => $unitWinBet,\n";
        $betting .= "\t\t'unitPlaBet' => $unitPlaBet,\n";
        $betting .= "\t\t'unitQplBet' => $unitQplBet,\n";
        $betting .= "\t\t'unitQinBet' => $unitQinBet,\n";
        $betting .= "\t],\n";
    }

    $betting .= "];\n";

    file_put_contents($outputFile, $betting);
}

getdata($raceDate, $totalRaces, $outputFile, $jockeyNamesAllRaces);
