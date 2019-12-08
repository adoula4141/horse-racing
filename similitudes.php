<?php

include __DIR__ . "/functions.php";
//arrange similar race dates, i.e. group those that the digits in the month and day numbers add to the same number.

$totals = [0, 1, 2, 3, 4, 5, 6, 7, 8];
$groups = [];
foreach ($totals as $value) {
	$groups[$value] = [];
}

$raceDates = getOpenRaceDates();

foreach ($raceDates as $raceDate) {
	$total = ($raceDate[4] + $raceDate[5] + $raceDate[6] + $raceDate[7]) % 9;
	$groups[$total][] = $raceDate;
}
var_dump($groups);
die();