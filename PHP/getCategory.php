<?php
// This script is the server side response to the Category lookup
//  ajax autosuggest call from the dynamicHeader.php
  require_once('../php_inc/xxx_connect.php');
  
  $dbh = mysql_connect(xxx_DB_HOST, xxx_DB_USER, xxx_DB_PASS);
  mysql_select_db(xxx_DB_NAME, $dbh);
  
	function autosuggest() {
		 $q = strtolower($_GET["q"]);
		 if (!$q) return;
			 $query = "SELECT category_name FROM categories where category_name like '%" . $q . "%'";
			 $results = mysql_query($query);
			 while($result = mysql_fetch_array($results)) {
				 $cat = $result['category_name'];
				 if (strpos(strtolower($cat), $q) !== false) {
				 	echo "$cat\n";
				 }
			 }
	 }

	if(!$dbh) {
		echo 'There was a problem connecting to the database';
	} else {
		autosuggest();
	 }
 ?>