<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate"."Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
	if($balance < 0) 
	{
		echo "Negative balance: $balance \n";
	}
	
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets[$raceNumber])) {
		continue;
	}
	$bets = $allBets[$raceNumber];
	if(!isset($bets['WIN'])) continue;
	$toWin = $bets['WIN'];
	if(isset($bets['unitWinBet'])) $unitWinBet = $bets['unitWinBet'];
	else $unitWinBet = 10;
	$winBets = $unitWinBet * count($toWin);
	
	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));
	if (!isset($raceDivParts[0])) {
		continue;
	}
	//1. Winning number
	$winnerLine = $raceDivParts[0];
	$winnerLineParts = explode("\t", $winnerLine);
	if($winnerLineParts[0] == 'WIN')
	{
		$balance -= $winBets;

		if(in_array($winnerLineParts[1], $toWin)) {
			$wonAmount = str_replace(",", "", $winnerLineParts[2]);
			$wonAmount = ($unitWinBet / 10) * $wonAmount;
			echo "Race: $raceNumber, winner: $winnerLineParts[1], list: " . implode(", ", $toWin) . ", won $wonAmount\n";
			$balance += $wonAmount;
		}
		
	}
}

echo "Final Balance: " . $balance . "\n\n";

