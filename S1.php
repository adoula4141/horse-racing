<?php


include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);
$inputFile = __DIR__ . "/data/racecard/$raceDate.php";
$jockeyNamesAllRaces = include($inputFile);

$totalRaces = count($jockeyNamesAllRaces);

$outputFile = "data/bets/$raceDate"."SetS1.php";

function getdata($raceDate, $totalRaces, $outputFile)
{
    $betting = "<?php\n\n";
    $betting .= "return [\n";

    $list = getSelection($raceDate, $totalRaces);
    $toWin = array_slice($list, 0, 2);

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

    $unitWinBet = 100;
    $unitPlaBet = 100;
    $unitQplBet = 10;
    $unitQinBet = 10;

    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(!raceExists($raceDate, $raceNumber)) continue;

        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $selection) . "],\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $selection) . "],\n";
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

getdata($raceDate, $totalRaces, $outputFile);