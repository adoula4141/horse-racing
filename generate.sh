for i in `ls data/racecard/`; do 
	php S1.php "${i%.php}"; 
	# php P.php "${i%.php}"; 
	# php Q.php "${i%.php}"; 
	# php D.php "${i%.php}"; 
	# php C.php "${i%.php}"; 
	# php D.php "${i%.php}"; 
	# php Q.php "${i%.php}"; 
	# php Q1.php "${i%.php}"; 
	# php Q2.php "${i%.php}"; 
	# php Q3.php "${i%.php}"; 
done
