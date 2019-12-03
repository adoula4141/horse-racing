for i in `ls data/racecard/`; do 
	php D.php "${i%.php}"; 
	# php Q1.php "${i%.php}"; 
	# php Q2.php "${i%.php}"; 
	# php Q3.php "${i%.php}"; 
	# php Q4.php "${i%.php}"; 
done
