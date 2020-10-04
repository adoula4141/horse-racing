for i in `ls data/racecard/`; do 
	php S1.php "${i%.php}"; 
	php D.php "${i%.php}"; 
	php all.php "${i%.php}"; 
	php s34.php "${i%.php}"; 
done
