for i in `ls data/racecard/`; do 
	php S1.php "${i%.php}"; 
done
