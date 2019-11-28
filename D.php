<?php


include __DIR__ . '/functions.php';

$totalRaces = 11; //default

$raceDate = trim($argv[1]);

$outputFile = "data/bets/$raceDate"."SetD.php";

function getdata($raceDate, $totalRaces, $outputFile)
{
    $betting = "<?php\n\n";
    $betting .= "return [\n";

    $list = getSelection($raceDate, $totalRaces, 'd');
    $list = array_slice($list, 0, 2);
    
    $toPlace = $list;
    $toWin = $list;
    $toQpl = $list;
    $toQin = $toQpl;
    
    for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) { 
        if(count($list) >=3) $toTrio = $list;
        else $toTrio = [];
        $toTce = $toTrio;

        if(count($list) >= 4) $toF4 = $list;
        else $toF4 = [];

        $betting .= "\t'R$raceNumber' => [\n";
        $betting .= "\t\t/**\n";
        $betting .= "\t\tRace $raceNumber\n";
        $betting .= "\t\t*/\n";

        $betting .= "\t\t'WIN' => [" . implode(", ", $toWin) . "],\n";
        $betting .= "\t\t'PLACE' => [" . implode(", ", $toPlace) . "],\n";
        $betting .= "\t\t'QUINELLA PLACE' => [" . implode(", ", $toQpl) . "],\n";
        $betting .= "\t\t'QUINELLA' => [" . implode(", ", $toQin) . "],\n";
        $betting .= "\t\t'FIRST 4' => [" . implode(", ", $toF4) ."],\n";
        $betting .= "\t\t'TRIO' => [" . implode(", ", $toTrio) ."],\n";
        $betting .= "\t\t'TIERCE' => [" . implode(", ", $toTce) ."],\n";

        $unitWinBet = 100;
        $unitPlaBet = 100;
        $unitQplBet = 10;
        $unitQinBet = 10;

        $winBets = $unitWinBet * count($toWin);
        $plaBets = $unitPlaBet * count($toPlace);
        $qplBets = $unitQplBet * combinations(count($toQpl), 2);
        $qinBets = $unitQinBet * combinations(count($toQin), 2);
        $f4Bets = 10 * combinations(count($toF4), 4);
        $trioBets = 10 * combinations(count($toTrio), 3);
        if(count($toTce) < 6){
            $tceBets = 10 * permutations(count($toTce), 3);
        }
        else{
            $tceBets = permutations(count($toTce), 3);   
        }

        $totalBets = $winBets + $plaBets + $f4Bets + $qinBets + $trioBets + $qplBets + $tceBets;

        $betting .= "\t\t'winBets' => $winBets,\n";
        $betting .= "\t\t'unitWinBet' => $unitWinBet,\n";
        $betting .= "\t\t'plaBets' => $plaBets,\n";
        $betting .= "\t\t'unitPlaBet' => $unitPlaBet,\n";
        $betting .= "\t\t'qplBets' => $qplBets,\n";
        $betting .= "\t\t'unitQplBet' => $unitQplBet,\n";
        $betting .= "\t\t'qinBets' => $qinBets,\n";
        $betting .= "\t\t'unitQinBet' => $unitQinBet,\n";
        $betting .= "\t\t'f4Bets' => $f4Bets,\n";
        $betting .= "\t\t'trioBets' => $trioBets,\n";
        $betting .= "\t\t'tceBets' => $tceBets,\n";
        $betting .= "\t\t'totalBets' => $totalBets\n";
        $betting .= "\t],\n";
    }

    $betting .= "];\n";

    file_put_contents($outputFile, $betting);
}

getdata($raceDate, $totalRaces, $outputFile);
