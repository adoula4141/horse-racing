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

    $listA = getSelection($raceDate, $totalRaces, 'a');
    $listB = getSelection($raceDate, $totalRaces, 'b');
    $listC = getSelection($raceDate, $totalRaces, 'c');
    $listD = getSelection($raceDate, $totalRaces, 'd');
    $listE = getSelection($raceDate, $totalRaces, 'e');
    $listF = getSelection($raceDate, $totalRaces, 'f');
    $listG = getSelection($raceDate, $totalRaces, 'g');
    $listH = getSelection($raceDate, $totalRaces, 'h');
    $listI = getSelection($raceDate, $totalRaces, 'i');

    $listR1 = [];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'k');  
    $listK1 = $horses; 
    if(isset($horses[0]) && !in_array($horses[0], $listR1)) $listR1[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR1)) $listR1[] = $horses[3];
    $horses = getWeights($raceDate, 1, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $listR1)) $listR1[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR1)) $listR1[] = $horses[3];
    $listO1 = $horses;

    $listR2 = [];
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'k');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR2)) $listR2[] = $horses[3];
    $listK2 = $horses;
    $horses = getWeights($raceDate, 2, 'jockeyNames', 'o');   
    if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
    if(isset($horses[3]) && !in_array($horses[3], $listR2)) $listR2[] = $horses[3];
    $listO2 = $horses;

    $toQplList = $listR2;
    $toQinList = $toQplList;

    $toRemove = array_values(array_unique(array_merge(
            array_intersect($listR1, $listR2),
            array_intersect($listR2, $listR1)
    )));

    $toPlace = $toWin;
    
    $toQpl = implode("-", $toWin) . " X " . implode("-", $listR2);
    $toQin = $toQpl;
    
    $unitWinBet = 100;
    $unitPlaBet = 100;
    $unitQplBet = 10;
    $unitQinBet = 10;

    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(!raceExists($raceDate, $raceNumber)) continue;
        $toTrio = "";
        $toTce = [];
        $toF4 = [];

        $betting .= "\t'$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t Selection A:\t" . implode("\t", $listA) . "\n";
        $betting .= "\t\t Selection B:\t" . implode("\t", $listB) . "\n";
        $betting .= "\t\t Selection C:\t" . implode("\t", $listC) . "\n";
        $betting .= "\t\t Selection D:\t" . implode("\t", $listD) . "\n";
        $betting .= "\t\t Selection E:\t" . implode("\t", $listE) . "\n";
        $betting .= "\t\t Selection F:\t" . implode("\t", $listF) . "\n";
        $betting .= "\t\t Selection G:\t" . implode("\t", $listG) . "\n";
        $betting .= "\t\t Selection H:\t" . implode("\t", $listH) . "\n";
        $betting .= "\t\t Selection I:\t" . implode("\t", $listI) . "\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => \"" . $toQpl . "\",\n";
        $betting .= "\t\t'QUINELLA PLACE LIST' => [" . implode(", ", $toQplList) . "],\n";
        $betting .= "\t\t'QUINELLA' => \"" . $toQin . "\",\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $toQinList) . "],\n";
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
