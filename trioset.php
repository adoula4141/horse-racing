<?php

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 2; $raceNumber++) { 
	if ($balance < 0) {
		echo "Negative balance: $balance \n";
	}
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets["R$raceNumber"])) {
		continue;
	}
	$bets = $allBets["R$raceNumber"];
	if(!isset($bets['TRIO'])) continue;
	$toQin = $bets['TRIO'];
	$qinBets = $bets['trioBets'];

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		if(strpos($raceDivPartsLine, "TRIO") !== false){
			$balance -= $qinBets;

			$lineParts = explode("\t", $raceDivParts[$key]);
			$winningQIN = explode(",", $lineParts[1]);
			$winningAmount = str_replace(",", "", $lineParts[2]);
			$isWinner = array_intersect($winningQIN, $toQin);
			$qinDiff = array_diff($winningQIN, $isWinner); 
			if(empty($qinDiff)) 
			{
				echo "Race: $raceNumber, Trio winner: $lineParts[1], won $lineParts[2]\n";
				$balance += $winningAmount;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";

