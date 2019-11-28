<?php

include __DIR__ . '/functions.php';

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
	if(!isset($bets['QUINELLA PLACE'])) continue;
	$banker = $bets['QUINELLA PLACE'];
	$bankerParts = explode(' X ', $banker); 
	$set1Parts = explode("-", $bankerParts[0]);
	$set2Parts = explode("-", $bankerParts[1]);
	$toQpl = [];
	foreach ($set1Parts as $val1) {
		foreach ($set2Parts as $val2) {
			if ($val1 !== $val2 && !in_array([$val1, $val2], $toQpl) && !in_array([$val2, $val1], $toQpl)) {
				if($val1 < $val2) $toQpl[] = [$val1, $val2];
				else $toQpl[] = [$val2, $val1];
			}
		}
	}
	if(isset($bets['unitQplBet'])) $unitQplBet = $bets['unitQplBet'];
	else $unitQplBet = 10;
	$qplBets = $unitQplBet * count($toQpl);

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		if(strpos($raceDivPartsLine, "QUINELLA PLACE") !== false){
			$balance -= $qplBets;

			$lineParts1 = explode("\t", $raceDivParts[$key]);
			$winningQPL1 = explode(",", $lineParts1[1]);
			sort($winningQPL1);
			$winningAmount1 = str_replace(",", "", $lineParts1[2]);
			$winningAmount1 = ($unitQplBet / 10) * $winningAmount1;
			$isWinner1 = in_array($winningQPL1, $toQpl);
			if($isWinner1)
			{
				echo "Race: $raceNumber, QPL winner: $lineParts1[1], won $lineParts1[2]\n";
				$balance += $winningAmount1;
			}
			$lineParts2 = explode("\t", $raceDivParts[$key + 1]);
			$winningQPL2 = explode(",", $lineParts2[0]);
			sort($winningQPL2);
			$winningAmount2 = str_replace(",", "", $lineParts2[1]);
			$winningAmount2 = ($unitQplBet / 10) * $winningAmount2;
			$isWinner2 = in_array($winningQPL2, $toQpl);
			if($isWinner2)
			{
				echo "Race: $raceNumber, QPL winner: $lineParts2[0], won $lineParts2[1]\n";
				$balance += $winningAmount2;
			}
			$lineParts3 = explode("\t", $raceDivParts[$key + 2]);
			$winningQPL3 = explode(",", $lineParts3[0]);
			sort($winningQPL3);
			$winningAmount3 = str_replace(",", "", $lineParts3[1]);
			$winningAmount3 = ($unitQplBet / 10) * $winningAmount3;
			$isWinner3 = in_array($winningQPL3, $toQpl);
			if($isWinner3)
			{
				echo "Race: $raceNumber, QPL winner: $lineParts3[0], won $lineParts3[1]\n";
				$balance += $winningAmount3;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";

