<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 9; $raceNumber++) { 
	if ($balance < 0) {
		echo "Negative balance: $balance \n";
	}
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets["R$raceNumber"])) {
		continue;
	}
	$bets = $allBets["R$raceNumber"];
	if(!isset($bets['FIRST 4'])) continue;
	$toF4 = $bets['FIRST 4'];
	$f4Bets = $bets['f4Bets'];

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		if(strpos($raceDivPartsLine, "FIRST 4") !== false){
			$balance -= $f4Bets;

			$lineParts = explode("\t", $raceDivParts[$key]);
			$winningf4 = explode(",", $lineParts[1]);
			$winningAmount = str_replace(",", "", $lineParts[2]);
			$isWinner = array_intersect($winningf4, $toF4);
			$f4Diff = array_diff($winningf4, $isWinner); 
			if(empty($f4Diff)) 
			{
				echo "Race: $raceNumber, FIRST 4 winner: $lineParts[1], won $lineParts[2]\n";
				$balance += $winningAmount;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";

