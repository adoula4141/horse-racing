<?php

include __DIR__ .'/functions.php';

$setNumber = trim($argv[1]);
$raceDate = trim($argv[2]);

$fileName = "data/results/$raceDate.html";
$content = file_get_contents($fileName);

$balance = 0;

$betsFile = "data/bets/$raceDate" . "Set$setNumber.php";
$allBets = include($betsFile);

for ($raceNumber=1; $raceNumber <= 11; $raceNumber++) { 
	if ($balance < 0) {
		echo "Negative balance: $balance \n";
	}
	//retrieve bets placed for race $raceNumber
	if (!isset($allBets[$raceNumber])) {
		continue;
	}
	$bets = $allBets[$raceNumber];

	if(!isset($bets['TRIO 1'])) continue;
	$toTrio = $bets['TRIO 1'];
	
	if(isset($bets['unitTrioBet'])) $unitTrioBet = $bets['unitTrioBet'];
	else $unitTrioBet = 10;

	$trioBets = $unitTrioBet * combinations(count($toTrio), 3);

	//retrieve results for race $raceNumber
	$raceStarts = strpos($content, "<R$raceNumber>");
	$raceEnds = strpos($content, "</R$raceNumber>");
	$raceDividends = substr($content, $raceStarts + 5, $raceEnds - $raceStarts - 4);
	$raceDivParts = array_values(array_filter(array_map('trim', explode("\n", $raceDividends))));

	foreach ($raceDivParts as $key=>$raceDivPartsLine) {
		if(strpos($raceDivPartsLine, "TRIO") !== false){
			$balance -= $trioBets;

			$lineParts = explode("\t", $raceDivParts[$key]);
			$winningTrio = explode(",", $lineParts[1]);
			$winningAmount = str_replace(",", "", $lineParts[2]);
			$isWinner = array_intersect($winningTrio, $toTrio);
			$trioDiff = array_diff($winningTrio, $isWinner); 
			if(empty($trioDiff)) 
			{
				echo "Race: $raceNumber, Trio winner: $lineParts[1], won $lineParts[2]\n";
				$balance += $winningAmount;
			}
			
		}
	}
}

echo "Final Balance: " . $balance . "\n\n";
