for i in `ls data/racecard/`; do 
	php S1.php "${i%.php}"; 
	php Q.php "${i%.php}"; 
done
