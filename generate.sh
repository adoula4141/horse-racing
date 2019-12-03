for i in `ls data/racecard/`; do 
	# php C.php "${i%.php}"; 
	# php D.php "${i%.php}"; 
	php Q.php "${i%.php}"; 
	# php Q1.php "${i%.php}"; 
	# php Q2.php "${i%.php}"; 
	# php Q3.php "${i%.php}"; 
	# php Q4.php "${i%.php}"; 
	# php Q5.php "${i%.php}"; 
	# php Q6.php "${i%.php}"; 
done
