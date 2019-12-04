<?php

function getSelection($raceDate, $totalRaces, $character = 'c')
{
    $selection = [];

    for ($i=1; $i <= $totalRaces ; $i++) { 
        if(!raceExists($raceDate, $i)) continue;
        $listR1 = [];
        for ($j=$i; $j <= $totalRaces ; $j++) { 
            if(!raceExists($raceDate, $j)) continue;
            $listR2 = [];
            $horses = getWeights($raceDate, $i, 'jockeyNames', $character);  
            if(isset($horses[0]) && !in_array($horses[0], $listR1)) $listR1[] = $horses[0];
            if(isset($horses[2]) && !in_array($horses[2], $listR1)) $listR1[] = $horses[2];

            $horses = getWeights($raceDate, $j, 'jockeyNames', $character);   
            if(isset($horses[0]) && !in_array($horses[0], $listR2)) $listR2[] = $horses[0];
            if(isset($horses[2]) && !in_array($horses[2], $listR2)) $listR2[] = $horses[2];

            $list = array_values(array_unique(array_merge(
                    array_intersect($listR1, $listR2),
                    array_intersect($listR2, $listR1)
            ))); 

            $selection = array_values(array_unique(array_merge($selection, $list)));  
        }
    }
    return $selection;
}

function getWeights($raceDate, $raceNumber, $search, $character)
{
    $jockeyNames = getJockeyNames($raceDate, $raceNumber);
    
    $weights = [];
    foreach ($jockeyNames as $horseNumber => $jockeyName) {
        if($search == 'jockeyNames') $name = jockeyName($jockeyName);
        elseif($search == 'jockeyLastNames') $name = jockeyLastName($jockeyName);
        else die('No search criterion specified in getWeights function!');
        $weights[$horseNumber] = getWeight($name, $character);
    }
    arsort($weights);
    return array_keys($weights);
}

function getJockeyNames($raceDate, $raceNumber)
{
    $inputFile = __DIR__ . "/data/racecard/$raceDate.php";
    if(!file_exists($inputFile)) return [];
    $jockeyNamesAllRaces = include($inputFile);
    return $jockeyNamesAllRaces[$raceNumber];
}

function raceExists($raceDate, $raceNumber)
{
    $inputFile = __DIR__ . "/data/racecard/$raceDate.php";
    if(!file_exists($inputFile)) return false;
    $jockeyNamesAllRaces = include($inputFile);
    return (isset($jockeyNamesAllRaces[$raceNumber]) && !empty($jockeyNamesAllRaces[$raceNumber]));
}

function getWeight($name, $character)   
{   
    $name = strtolower(str_replace(" ", "", $name));    
    $occurences = 0;    
    for ($i=0; $i < strlen($name); $i++) {  
        if($name[$i] == $character) $occurences ++; 
    }   
    return $occurences; 
}

function jockeyName($jockeyName)
{
    $str = preg_replace("/\([^)]+\)/","",$jockeyName);
    return $str;
}

function jockeyLastName($jockeyName)
{
    $str = preg_replace("/\([^)]+\)/","",$jockeyName);
    $parts = explode(" ", $str);
    return $parts[count($parts) - 1];
}

function getWinBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    $winnerLine = $raceDivParts[0];
    $winnerLineParts = explode("\t", $winnerLine);
    if($winnerLineParts[0] == 'WIN') {
        if(is_array($selected)) $balance -= $unitBets * count($selected);
            else $balance -= $unitBets;
        if((is_numeric($selected) && $winnerLineParts[1] == $selected)
                || (is_array($selected) && in_array($winnerLineParts[1], $selected))
            ) 
        {
            $balance += $unitBets / 10 * str_replace(",", "", $winnerLineParts[2]);
        }
    }

    return $balance;
}

function plaBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['PLACE'])) continue;
        $selected = $bets['PLACE'];
        $totalWon += getPlaBalance($raceDate, $raceNumber, $selected, $bets['unitPlaBet']);
    }
    return $totalWon;
}

function winBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['WIN'])) continue;
        $selected = $bets['WIN'];
        $totalWon += getWinBalance($raceDate, $raceNumber, $selected, $bets['unitWinBet']);
    }
    return $totalWon;
}

function qplBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['QUINELLA PLACE'])) continue;
        $selected = $bets['QUINELLA PLACE'];
        if(isset($bets['unitQplBet'])) $unitQplBet = $bets['unitQplBet'];
        else $unitQplBet = 10;
        $totalWon += getQplBalance($raceDate, $raceNumber, $selected, $unitQplBet);
    }
    return $totalWon;
}

function qinBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['QUINELLA'])) continue;
        $selected = $bets['QUINELLA'];
        if(isset($bets['unitQinBet'])) $unitQinBet = $bets['unitQinBet'];
        else $unitQinBet = 10;
        $totalWon += getQinBalance($raceDate, $raceNumber, $selected, $unitQinBet);
    }
    return $totalWon;
}


function tceBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['TIERCE'])) continue;
        $selected = $bets['TIERCE'];
        if(empty($selected)) continue;
        $totalWon += getTceBalance($raceDate, $raceNumber, $selected);
    }
    return $totalWon;
}

function getPlaBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        $winnerLineParts = explode("\t", $raceDivPartsLine);
        if($winnerLineParts[0] == 'PLACE')
        {
            if(is_array($selected)) $balance -= $unitBets * count($selected);
            else $balance -= $unitBets;
            if((is_numeric($selected) && $winnerLineParts[1] == $selected)
                || (is_array($selected) && in_array($winnerLineParts[1], $selected))
            ) {
                $balance += $unitBets / 10 * str_replace(",", "", $winnerLineParts[2]);
            }
            $lineParts2 = explode("\t", $raceDivParts[$key + 1]);
            if((is_numeric($selected) && $lineParts2[0] == $selected)
                || (is_array($selected) && in_array($lineParts2[0], $selected))
            ) {
                $balance += $unitBets / 10 * str_replace(",", "", $lineParts2[1]);
            }
            $lineParts3 = explode("\t", $raceDivParts[$key + 2]);
            if((is_numeric($selected) && $lineParts3[0] == $selected)
                || (is_array($selected) && in_array($lineParts3[0], $selected))
            ) {
                $balance += $unitBets / 10 * str_replace(",", "", $lineParts3[1]);
            }
        }
    }

    return $balance;
}

function getQplBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        if(strpos($raceDivPartsLine, "QUINELLA PLACE") !== false){
            $qplBets = $unitBets * combinations(count($selected), 2);
            $balance -= $qplBets;
            $lineParts1 = explode("\t", $raceDivParts[$key]);
            $winningQPL1 = explode(",", $lineParts1[1]);
            $winningAmount1 = str_replace(",", "", $lineParts1[2]);
            $isWinner1 = array_intersect($winningQPL1, $selected);
            $qplDiff1 = array_diff($winningQPL1, $isWinner1); 
            if(empty($qplDiff1)) 
            {
                $balance += $unitBets / 10 * $winningAmount1;
            }
            $lineParts2 = explode("\t", $raceDivParts[$key + 1]);
            $winningQPL2 = explode(",", $lineParts2[0]);
            $winningAmount2 = str_replace(",", "", $lineParts2[1]);
            $isWinner2 = array_intersect($winningQPL2, $selected);
            $qplDiff2 = array_diff($winningQPL2, $isWinner2); 
            if(empty($qplDiff2)) 
            {
                $balance += $unitBets / 10 * $winningAmount2;
            }
            $lineParts3 = explode("\t", $raceDivParts[$key + 2]);
            $winningQPL3 = explode(",", $lineParts3[0]);
            $winningAmount3 = str_replace(",", "", $lineParts3[1]);
            $isWinner3 = array_intersect($winningQPL3, $selected);
            $qplDiff3 = array_diff($winningQPL3, $isWinner3); 
            if(empty($qplDiff3)) 
            {
                $balance += $unitBets / 10 * $winningAmount3;
            }
        }
    }
    return $balance;
}
function getQinBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        if(strpos($raceDivPartsLine, "QUINELLA") !== false 
            && strpos($raceDivPartsLine, "QUINELLA PLACE") === false){
            $qinBets = $unitBets * combinations(count($selected), 2);
            $balance -= $qinBets;
            $lineParts1 = explode("\t", $raceDivParts[$key]);
            $winningQPL1 = explode(",", $lineParts1[1]);
            $winningAmount1 = str_replace(",", "", $lineParts1[2]);
            $isWinner1 = array_intersect($winningQPL1, $selected);
            $qplDiff1 = array_diff($winningQPL1, $isWinner1); 
            if(empty($qplDiff1)) 
            {
                $balance += $unitBets / 10 * $winningAmount1;
            }
        }
    }
    return $balance;
}
function getTrioBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        if(strpos($raceDivPartsLine, "TRIO") !== false){
            $trioBets = $unitBets * combinations(count($selected), 3);
            $balance -= $trioBets;
            $lineParts1 = explode("\t", $raceDivParts[$key]);
            $winningQPL1 = explode(",", $lineParts1[1]);
            $winningAmount1 = str_replace(",", "", $lineParts1[2]);
            $isWinner1 = array_intersect($winningQPL1, $selected);
            $qplDiff1 = array_diff($winningQPL1, $isWinner1); 
            if(empty($qplDiff1)) 
            {
                $balance += $unitBets / 10 * $winningAmount1;
            }
        }
    }
    return $balance;
}

function trioBalance($raceDate, $method)
{
    //1.get the bets
    $betsFile = "data/bets/$raceDate" . "Set$method.php";
    $allBets = include($betsFile);
    $totalWon = 0;

    for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
        //retrieve bets placed for race $raceNumber
        if (!isset($allBets[$raceNumber])) {
            continue;
        }
        $bets = $allBets[$raceNumber];
        if(!isset($bets['TRIO'])) continue;
        $selected = $bets['TRIO'];
        if(empty($selected)) continue;
        if(isset($bets['unitTrioBet'])) $unitTrioBet = $bets['unitTrioBet'];
        else $unitTrioBet = 10;
        $totalWon += getTrioBalance($raceDate, $raceNumber, $selected, $unitTrioBet);
    }
    return $totalWon;
}

function getTceBalance($raceDate, $raceNumber, $selected, $unitBets = 10)
{
    if(empty($selected)) return 0;
    $balance = 0;
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) return $balance;
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        if(strpos($raceDivPartsLine, "TIERCE") !== false){
            if(count($selected) < 6){
                $tceBets = $unitBets * permutations(count($selected), 3);
            }
            else{
                $tceBets = permutations(count($selected), 3);
            }
            $balance -= $tceBets;
            $lineParts1 = explode("\t", $raceDivParts[$key]);
            $winningQPL1 = explode(",", $lineParts1[1]);
            $winningAmount1 = str_replace(",", "", $lineParts1[2]);
            $isWinner1 = array_intersect($winningQPL1, $selected);
            $qplDiff1 = array_diff($winningQPL1, $isWinner1); 
            if(empty($qplDiff1)) 
            {
                if(count($selected) < 6){
                    $balance += $unitBets / 10 * $winningAmount1;
                }
                else{
                    $balance += $unitBets / 100 * $winningAmount1;
                }
            }
        }
    }
    return $balance;
}

function getQuartetResult($raceDate, $raceNumber)
{
    $winningQuartet = [];
    $resultsFile = __DIR__ . "/data/results/$raceDate.html";
    if(!file_exists($resultsFile)) $winningQuartet = [];
    $content = file_get_contents($resultsFile);
    //retrieve results for race $raceNumber
    $raceStarts = strpos($content, "<R$raceNumber>");
    $raceEnds = strpos($content, "</R$raceNumber>");
    $raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
    $raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
    foreach ($raceDivParts as $key=>$raceDivPartsLine) {
        if(strpos($raceDivPartsLine, "QUARTET") !== false){
            $lineParts = explode("\t", $raceDivParts[$key]);
            $winningQuartet = explode(",", $lineParts[1]);
        }
    }
    return $winningQuartet;
}

function factorial(int $n)
{
    $factorial = 1;
    while ($n > 0) {
        $factorial *= $n;
        $n--;
    }
    return $factorial;
}

function combinations(int $n, int $k)
{
    if($n == 0 || $n < $k) return 0;
    $nominator   = factorial($n);
    $denominator = factorial($n - $k) * factorial($k);
    return $nominator / $denominator;
}

function permutations(int $n, int $k)
{
    if($n == 0 || $n < $k) return 0;
    $nominator   = factorial($n);
    $denominator = factorial($n - $k);
    return $nominator / $denominator;
}

function sortByLengthASC($a,$b){
    return strlen($b)-strlen($a);
}

function sortByLengthDESC($a,$b){
    return strlen($a)-strlen($b);
}
