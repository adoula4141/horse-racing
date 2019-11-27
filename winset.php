<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate"."Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 8; $raceNumber++) { 
	// if($balance > 100) 
	// {
	// 	echo "Final balance: $balance \n";
	// 	exit();
	// }
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets["R$raceNumber"])) {
		continue;
	}
	$bets = $allBets["R$raceNumber"];
	if(!isset($bets['WIN'])) continue;
	$toWin = $bets['WIN'];
	$winBets = $bets['winBets'];
	if(isset($bets['unitWinBet'])) $unitWinBet = $bets['unitWinBet'];
	else $unitWinBet = 10;
	
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

