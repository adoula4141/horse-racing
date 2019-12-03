<?php


include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);
$inputFile = __DIR__ . "/data/racecard/$raceDate.php";
$jockeyNamesAllRaces = include($inputFile);

$totalRaces = count($jockeyNamesAllRaces);

$outputFile = "data/bets/$raceDate"."SetQ.php";

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

        $qpl1 = [];
        if(isset($list1[1]) && !in_array($list1[1], $qpl1)) $qpl1[] = $list1[1];
        if(isset($list1[2]) && !in_array($list1[2], $qpl1)) $qpl1[] = $list1[2];
        if(isset($list2[1]) && !in_array($list2[1], $qpl1)) $qpl1[] = $list2[1];

        $qpl2 = [];
        if(isset($list2[0]) && !in_array($list2[0], $qpl2)) $qpl2[] = $list2[0];
        if(isset($list2[1]) && !in_array($list2[1], $qpl2)) $qpl2[] = $list2[1];
        if(isset($list3[0]) && !in_array($list3[0], $qpl2)) $qpl2[] = $list3[0];

        $qpl3 = [];
        if(isset($list2[1]) && !in_array($list2[1], $qpl3)) $qpl3[] = $list2[1];
        if(isset($list3[1]) && !in_array($list3[1], $qpl3)) $qpl3[] = $list3[1];
        if(isset($list3[2]) && !in_array($list3[2], $qpl3)) $qpl3[] = $list3[2];

        $numberOfHorses = count($jockeyNamesAllRaces[$raceNumber]);
        $list4 = [];

        for ($i = 1; $i <= $numberOfHorses ; $i++) { 
            if(!in_array($i, $list)) $list4[] = $i;
        }

        $qpl4 = [];
        if(isset($list1[3]) && !in_array($list1[3], $qpl4)) $qpl4[] = $list1[3];
        if(isset($list2[3]) && !in_array($list2[3], $qpl4)) $qpl4[] = $list2[3];
        if(isset($list4[1]) && !in_array($list4[1], $qpl4)) $qpl4[] = $list4[1];

        $selection = array_values(array_unique(array_merge($qpl1, $qpl4)));

        $toWin = [];
        if(isset($selection[5])) $toWin[] = $selection[5];
        elseif(isset($selection[4])) $toWin[] = $selection[4];

        $toPlace = $toWin;

        $toTrio = array_slice($list, 1, 5);

        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t QPL 1:\t" . implode(", ", $qpl1) . "\n";
        $betting .= "\t\t QPL 2:\t" . implode(", ", $qpl2) . "\n";
        $betting .= "\t\t QPL 3:\t" . implode(", ", $qpl3) . "\n";
        $betting .= "\t\t QPL 4:\t" . implode(", ", $qpl4) . "\n";
        $betting .= "\t\t List 1:\t" . implode(", ", $list1) . "\n";
        $betting .= "\t\t List 2:\t" . implode(", ", $list2) . "\n";
        $betting .= "\t\t List 3:\t" . implode(", ", $list3) . "\n";
        $betting .= "\t\t List 4:\t" . implode(", ", $list4) . "\n";

        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $selection) . "],\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $selection) . "],\n";
        $betting .= "\t\t'TRIO' => [" . implode(", ", $toTrio) ."],\n";
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
