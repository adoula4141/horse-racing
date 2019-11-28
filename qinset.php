<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber = 1; $raceNumber <= 11; $raceNumber++) { 
	if ($balance < 0) {
		echo "Negative balance: $balance \n";
	}
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets[$raceNumber])) {
		continue;
	}
	$bets = $allBets[$raceNumber];
	if(!isset($bets['QUINELLA'])) continue;
	$banker = $bets['QUINELLA'];
	$bankerParts = explode(' X ', $banker); 
	$set1Parts = explode("-", $bankerParts[0]);
	$set2Parts = explode("-", $bankerParts[1]);
	$toQin = [];
	foreach ($set1Parts as $val1) {
		foreach ($set2Parts as $val2) {
			if ($val1 !== $val2 && !in_array([$val1, $val2], $toQin) && !in_array([$val2, $val1], $toQin)) {
				if($val1 < $val2) $toQin[] = [$val1, $val2];
				else $toQin[] = [$val2, $val1];
			}
		}
	}
	if(isset($bets['unitQinBet'])) $unitQinBet = $bets['unitQinBet'];
	else $unitQinBet = 10;
	$qinBets = $unitQinBet * count($toQin);

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		if(strpos($raceDivPartsLine, "QUINELLA") !== false
			&& strpos($raceDivPartsLine, "QUINELLA PLACE") === false
		){
			$balance -= $qinBets;

			$lineParts = explode("\t", $raceDivParts[$key]);
			$winningQIN = explode(",", $lineParts[1]);
			sort($winningQIN);
			$winningAmount = str_replace(",", "", $lineParts[2]);
			$winningAmount = (10/$unitQinBet) * $winningAmount;
			$isWinner = in_array($winningQIN, $toQin);
			if($isWinner)
			{
				echo "Race: $raceNumber, QIN winner: $lineParts[1], won $lineParts[2]\n";
				$balance += $winningAmount;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";

