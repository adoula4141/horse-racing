<?php

include __DIR__ . "/functions.php";

//1. get the list of race dates
$raceDates = [];
$resultsDir = __DIR__ . "/data/results";
if ($handle = opendir($resultsDir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if($entry !== "template.html"){
            	$raceDates[] = substr($entry, 0, -5);
            }
        }
    }
    closedir($handle);
}

asort($raceDates);


//2. Get the balance for each racing date, betting style and set.
$methods = [ 'pla_Q', 'qpl_D', 'qpl_Q1', 'qpl_Q2', 'qpl_Q3', 'qpl_Q4' ];

$balancesMatrix = [];
$header = ["race_date"];
foreach($methods as $method){ 
	$header[] = $method;
}
$header[] = "line_sum";
$balancesMatrix[] = $header;

$outputFile = 'selected.html';

for ($key=0; $key < count($raceDates); $key++) { 
	$raceDate = $raceDates[$key];
	$header = [$raceDate];
	$lineSum = 0;
	foreach($methods as $method){
		$methodParts = explode("_", $method);
		$style = $methodParts[0];
		$set = $methodParts[1];
		switch ($style) {
			case 'pla':
				$amount = plaBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'win':
				$amount = winBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'qin':
				$amount = qinBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'qpl':
				$amount = qplBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'trio':
				$amount = trioBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'tce':
				$amount = tceBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'f4':
				$amount = f4Balance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;

			case 'quartet':
				$amount = quartetBalance($raceDate, $set);
				$header[] = $amount;
				$lineSum += $amount;
				break;
			
			default:
				# code...
				break;
		}
			
	}
	$header[] = $lineSum;
	$balancesMatrix[] = $header;
}

$sum = ["Sums"];
$avg = ["Averages"];

for ($i = 1; $i < count($balancesMatrix); $i++) { 
	for ($j=1; $j <= count($methods) + 1; $j++) { 
		if(!isset($sum[$j])) $sum[$j] = $balancesMatrix[$i][$j];
		else $sum[$j] += $balancesMatrix[$i][$j];
	}
	for ($j=1; $j <= count($methods) + 1; $j++) {
		$avg[$j] = $sum[$j] / count($methods);
	}
}

$fp = $outputFile;

$htmlTable = "<html>\n\t";
$htmlTable .= '<script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
';
$htmlTable .=  "\t\n" ;

$htmlTable .= "<table class=\"sortable\">\n";

foreach ($balancesMatrix as $key => $line) {
	if($key == 0) {
		$htmlTable .= "\t<tr>\n";
	}
	else {
		$htmlTable .= "\t<tr class=\"item\">\n";
	}
	foreach ($line as $value) {
		$htmlTable .= "\t\t<td>" . $value . "</td>\n";
	}
	$htmlTable .= "\t</tr>\n";
}

$htmlTable .= "\t<tr class=\"item\">\n";
for ($i=0; $i < count($sum); $i++) { 
	$htmlTable .= "\t\t<td>$sum[$i]</td>\n";
}
$htmlTable .= "\t</tr>\n";
$htmlTable .= "\t<tr class=\"item\">\n";
for ($i=0; $i < count($avg); $i++) { 
	$htmlTable .= "\t\t<td>$avg[$i]</td>\n";
}
$htmlTable .= "\t</tr>\n";
$htmlTable .= "</table>\n";
$htmlTable .= "</html>";

file_put_contents($fp, $htmlTable);
