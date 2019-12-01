<?php


include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);
$inputFile = __DIR__ . "/data/racecard/$raceDate.php";
$jockeyNamesAllRaces = include($inputFile);

$totalRaces = count($jockeyNamesAllRaces);

$outputFile = "data/bets/$raceDate"."SetC.php";

function getdata($raceDate, $totalRaces, $outputFile)
{
    $betting = "<?php\n\n";
    $betting .= "return [\n";

    $list = getSelection($raceDate, $totalRaces);
    $toWin = array_slice($list, 0, 2);

    $listR1 = [];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'k');   
    if(isset($horses[0]) && !in_array($horses[0], $listR1)) $listR1[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR1)) $listR1[] = $horses[3];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $listR1)) $listR1[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR1)) $listR1[] = $horses[3];

    $listR2 = [];
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'k');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR2)) $listR2[] = $horses[3];
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR2)) $listR2[] = $horses[3];

    $toPlace = array_values(array_unique(array_merge(
            array_intersect($listR1, $listR2),
            array_intersect($listR2, $listR1)
    )));

    if(count($toPlace) == 0) {
        $list = [];
        $toWin = [];
    }
    else $list = $toPlace;
    $toPlace = $list;

    //remove all intersections between $toWin and $toPlace
    $intersection = array_filter(array_values(array_merge(array_intersect($toWin, $toPlace), array_intersect($toPlace, $toWin))));
    $toWin = array_diff($toWin, $intersection);
    $toPlace = array_diff($toPlace, $intersection);

    $list = $toPlace;
    
    $toQpl = implode("-", $toWin) . " X " . implode("-", $toPlace);
    $toQin = $toQpl;
    
    $unitWinBet = 100;
    $unitPlaBet = 100;
    $unitQplBet = 10;
    $unitQinBet = 10;

    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(!raceExists($raceDate, $raceNumber)) continue;
        $horses = getWeights($raceDate, $raceNumber, 'jockeyNames', 'a');
        $list = array_slice($horses, 0, 3);
        $toTrio = $toQpl . " X " . implode("-", $list);
        $toTce = [];

        if(count($list) >= 4) $toF4 = $list;
        else $toF4 = [];

        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => \"" . $toQpl . "\",\n";
        // $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $toQplList) . "],\n";
        $betting .= "\t\t'QUINELLA' => \"" . $toQin . "\",\n";
        // $betting .= "\t\t'QUINELLA' => [" . implode(", ", $toQinList) . "],\n";
        $betting .= "\t\t'TRIO' => \"" . $toTrio ."\",\n";
        // $betting .= "\t\t'TRIO LIST' => [" . implode(", ", $toTrioList) ."],\n";
        $betting .= "\t\t'TIERCE' => [" . implode(", ", $toTce) ."],\n";
        $betting .= "\t\t'FIRST 4' => [" . implode(", ", $toF4) ."],\n";
        
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
