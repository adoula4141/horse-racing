<?php

$raceDate = trim($argv[1]);

if(strlen($raceDate) == 4) $raceDate = "2019$raceDate";

if (isset($argv[2])) {
	$venue = trim($argv[2]);
}
else {
	$venue = "ST";
}

$totalRaces = 11;

$folderName = "data/racecard/$raceDate";

if(!file_exists($folderName)) mkdir($folderName);

for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) {
	
	$fileName = "$folderName/$raceNumber.html";

	$url = "https://racing.hkjc.com/racing/info/meeting/RaceCard/english/Local/$raceDate/$venue/$raceNumber";

	$content = file_get_contents($url);

	while (empty($content)) {
		$content = file_get_contents($url);
	}

	$first_step = explode( '<!-- for sizing optimization -->' , $content );

	$horsesTable = "";

	for ($i=1; $i < count($first_step); $i++) { 
		$horsesTable .= $first_step[$i];
	}

	if(empty($horsesTable))
	{
		file_put_contents($fileName, $first_step[1]);
	}

	$last_step = explode('</table>', $horsesTable);
	file_put_contents("$fileName", $last_step[0]);
}
