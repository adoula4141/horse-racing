<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 11; $raceNumber++) { 
	// if($balance > 100) 
	// {
	// 	echo "Final balance: $balance \n";
	// 	exit();
	// }
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets[$raceNumber])) {
		continue;
	}
	$bets = $allBets[$raceNumber];
	if(!isset($bets['PLACE'])) continue;
	$toPlace = $bets['PLACE'];
	if(isset($bets['unitPlaBet'])) $unitPlaBet = $bets['unitPlaBet'];
	else $unitPlaBet = 10;
	$plaBets = $unitPlaBet * count($toPlace);

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		$winnerLineParts = explode("\t", $raceDivPartsLine);
		if($winnerLineParts[0] == 'PLACE')
		{
			$balance -= $plaBets;

			if(in_array($winnerLineParts[1], $toPlace)) {
				$amount = str_replace(",", "", $winnerLineParts[2]);
				$amount = ($unitPlaBet / 10 ) * $amount;
				echo "Race: $raceNumber, PLA winner: $winnerLineParts[1], list: " . implode(", ", $toPlace) . ", won $amount\n";
				$balance += $amount;
				
			}
			$lineParts2 = explode("\t", $raceDivParts[$key + 1]);
			$winningPLA2 = $lineParts2[0];
			$winningAmount2 = str_replace(",", "", $lineParts2[1]);
			if(in_array($winningPLA2, $toPlace))
			{
				$amount = ($unitPlaBet / 10 ) * $winningAmount2;
				echo "Race: $raceNumber, PLA winner: $lineParts2[0], won $amount\n";
				$balance += $amount;
			}
			$lineParts3 = explode("\t", $raceDivParts[$key + 2]);
			$winningPLA3 = $lineParts3[0];
			$winningAmount3 = str_replace(",", "", $lineParts3[1]);
			if(in_array($winningPLA3, $toPlace))
			{
				$amount = ($unitPlaBet / 10 ) * $winningAmount3;
				echo "Race: $raceNumber, PLA winner: $lineParts3[0], won $amount\n";
				$balance += $amount;
			}
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";

return $balance;
