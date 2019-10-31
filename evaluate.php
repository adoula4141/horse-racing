<?php

include __DIR__ . "/functions.php";

/**
THIS IS THE MAIN PROGRAM OF THE APPLICATION.
IT GENERATES A MATRIX OF 5 * 7 = 35 COLUMNS EACH REPRESENTING ONE OF THE 5 BETTING STYLES:
	1. WIN
	2. PLACE
	3. QIN
	4. QPL
	5. TRIO
AND EACH OF THE 7 BET SETS (set1.php, set2.php,.... set7.php).
THE LINES REPRESENTS THE RACE DATES.
A VALUE OF THE MATRIX IS THE FINAL BALANCE USING THAT BETTING METHOD FOR THAT DAY.
FOR EXAMPLE, LINE 0210 AND COLUMN 7(EIGTH COLUMN) IS THE FINAL BALANCE FOR BETTING PLACE WITH SET 1 ON 02/10 RACING DAY.
THE GOAL IS TO DERIVE A BETTING STRATEGY TO COMPENSATE FOR THE LOSSES OF ONE METHOD BY PLAYING IT ALONG WITH ANOTHER ONE OF THE 37TH METHODS
*/

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

// $raceDates = ["0101", "0202"];

//2. Get the balance for each racing date, betting style and set.
$styles = [ 'trio', 'tce'];
// $styles = [ 'qin', 'qpl'];
// $styles = [ 'win', 'pla' ];
// $methods = [ 'D', 'S34', 'A'];
$methods = [ 'D', 'A' ];

$totalCount = count($styles) * count($methods);

$balancesMatrix = [];
$header = ["race_date"];
foreach($methods as $method){ 
	foreach ($styles as $style) {
		$header[] = $style . "_set" . $method;
	}
}
$header[] = "line_sum";
$balancesMatrix[] = $header;

$outputFile = 'evaluation.html';

for ($key=0; $key < count($raceDates); $key++) { 
	$raceDate = $raceDates[$key];
	$header = [$raceDate];
	$lineSum = 0;
	foreach($methods as $method){
		foreach ($styles as $style) {
			switch ($style) {
				case 'pla':
					$amount = plaBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'win':
					$amount = winBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'qin':
					$amount = qinBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'qpl':
					$amount = qplBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'trio':
					$amount = trioBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'tce':
					$amount = tceBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'f4':
					$amount = f4Balance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;

				case 'quartet':
					$amount = quartetBalance($raceDate, $method);
					$header[] = $amount;
					$lineSum += $amount;
					break;
				
				default:
					# code...
					break;
			}
			
		}
	}
	$header[] = $lineSum;
	$balancesMatrix[] = $header;
}

$sum = ["Sums"];
$avg = ["Averages"];

for ($method = 1; $method < count($balancesMatrix); $method++) { 
	for ($j=1; $j <= $totalCount; $j++) { 
		if(!isset($sum[$j])) $sum[$j] = $balancesMatrix[$method][$j];
		else $sum[$j] += $balancesMatrix[$method][$j];
	}
	for ($j=1; $j <= $totalCount; $j++) {
		$avg[$j] = $sum[$j] / $totalCount;
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
for ($method=0; $method < count($sum); $method++) { 
	$htmlTable .= "\t\t<td>$sum[$method]</td>\n";
}
$htmlTable .= "\t</tr>\n";
$htmlTable .= "\t<tr class=\"item\">\n";
for ($method=0; $method < count($avg); $method++) { 
	$htmlTable .= "\t\t<td>$avg[$method]</td>\n";
}
$htmlTable .= "\t</tr>\n";
$htmlTable .= "</table>\n";
$htmlTable .= "</html>";

file_put_contents($fp, $htmlTable);
