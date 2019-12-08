<?php

include __DIR__ . '/functions.php';

$raceDate = trim($argv[1]);

if(strlen($raceDate) == 4) $raceDate = "2019$raceDate";

if (isset($argv[2])) {
	$venue = trim($argv[2]);
}
else {
	$venue = "ST";
}

$totalRaces = 11;

$outputFileName = "data/racecard/$raceDate.php";

$racecard = "<?php\n\n";
$racecard .= "/**\n";
$racecard .= "\t Jockey Names\n";
$racecard .= "*/\n";
$racecard .= "return [\n";

for ($raceNumber = 1; $raceNumber <= $totalRaces; $raceNumber++) {
	$racecardPart = "\t$raceNumber => [\n";
    $racecardPart .= "\t\t/**\n";
    $racecardPart .= "\t\tRace $raceNumber\n";
    $racecardPart .= "\t\t*/\n";

	$url = "https://racing.hkjc.com/racing/info/meeting/RaceCard/english/Local/$raceDate/$venue/$raceNumber";

	$content = file_get_contents($url);

	$first_step = explode( "<td>" , $content );

	$jockeyNames = [];
	$horseNumber = 1;
	$withdrawns = [];
	for ($i=1; $i < count($first_step); $i++) { 
        if(strpos($first_step[$i], 'color_red') !== false) { 
        	$withdrawns[] = $horseNumber; 
       	}
		if (strpos($first_step[$i], 'jockeycode')) {
			$jockeyName = strip_tags($first_step[$i]);
			$jockeyName = jockeyName($jockeyName);
			$jockeyName = trim(preg_replace('/[\t|\n|\s{2,}]/', '', $jockeyName));
			if(!empty($jockeyName)) {
				echo $jockeyName . "\n"; //echo for debugging purposes
				$jockeyNames[] = $jockeyName;
				if(empty($withdrawns) || ! in_array($horseNumber, $withdrawns)){
					$racecardPart .= "\t\t$horseNumber => \"$jockeyName\",\n";
				}
				$horseNumber ++;
			}
		}
	}

	$racecardPart .= "\t],\n";
	if(!empty($jockeyNames)) $racecard .= $racecardPart;
}
$racecard .= "];\n";
file_put_contents($outputFileName, $racecard);
