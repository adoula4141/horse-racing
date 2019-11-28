for i in `ls data/racecard/`; do php A.php "${i%.php}"; php B.php "${i%.php}"; php C.php "${i%.php}"; php D.php "${i%.php}"; php G.php "${i%.php}"; php E.php "${i%.php}"; php F.php "${i%.php}"; done
