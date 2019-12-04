<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 7; $raceNumber++) { 
	if ($balance < 0) {
		echo "Negative balance: $balance \n";
	}
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets["$raceNumber"])) {
		continue;
	}
	$bets = $allBets["$raceNumber"];
	if(!isset($bets['QUINELLA PLACE'])) continue;
	$toQpl = $bets['QUINELLA PLACE'];
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
			$winningAmount1 = str_replace(",", "", $lineParts1[2]);
			$winningAmount1 = ($unitQplBet / 10) * $winningAmount1;
			$isWinner1 = array_intersect($winningQPL1, $toQpl);
			$qplDiff1 = array_diff($winningQPL1, $isWinner1); 
			if(empty($qplDiff1)) 
			{
				echo "Race: $raceNumber, QPL winner: $lineParts1[1], won $lineParts1[2]\n";
				$balance += $winningAmount1;
			}
			$lineParts2 = explode("\t", $raceDivParts[$key + 1]);
			$winningQPL2 = explode(",", $lineParts2[0]);
			$winningAmount2 = str_replace(",", "", $lineParts2[1]);
			$winningAmount2 = ($unitQplBet / 10) * $winningAmount2;
			$isWinner2 = array_intersect($winningQPL2, $toQpl);
			$qplDiff2 = array_diff($winningQPL2, $isWinner2); 
			if(empty($qplDiff2)) 
			{
				echo "Race: $raceNumber, QPL winner: $lineParts2[0], won $lineParts2[1]\n";
				$balance += $winningAmount2;
			}
			$lineParts3 = explode("\t", $raceDivParts[$key + 2]);
			$winningQPL3 = explode(",", $lineParts3[0]);
			$winningAmount3 = str_replace(",", "", $lineParts3[1]);
			$winningAmount3 = ($unitQplBet / 10) * $winningAmount3;
			$isWinner3 = array_intersect($winningQPL3, $toQpl);
			$qplDiff3 = array_diff($winningQPL3, $isWinner3); 
			if(empty($qplDiff3)) 
			{
				echo "Race: $raceNumber, QPL winner: $lineParts3[0], won $lineParts3[1]\n";
				$balance += $winningAmount3;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";
