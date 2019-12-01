for i in `ls data/racecard/`; do 
	php C.php "${i%.php}"; 
	# php s2.php "${i%.php}"; 
	# php s3.php "${i%.php}"; 
	# php s5.php "${i%.php}"; 
done
