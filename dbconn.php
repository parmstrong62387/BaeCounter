<?php

	function connect() {
		$dbhost = "sql213.hostingmyself.com";
		$dbuser = "hmsfo_18881848";
		$dbpass = "freescripthostpass";
		$dbname = "hmsfo_18881848_counter";

		//Connect to MySQL Server
		$conn = mysql_connect($dbhost, $dbuser, $dbpass);

		//Select Database
		mysql_select_db($dbname) or die(mysql_error());
	}

	function isConnected() {
		return @mysql_ping() ? true : false;
	}

	function getCounters() {
		if (!isConnected()) {
			connect();
		}

		//build query
	   $query = "SELECT * FROM counters";
	   
	   //Execute query
	   $qry_result = mysql_query($query) or die(mysql_error());

	   $counters = array();

	   while($row = mysql_fetch_array($qry_result)) {
	     $counters[$row[counter_name]] = $row[count];
	   }

	   return $counters;
	}

	function updateCounter($counterToUpdate) {
		if (!isConnected()) {
			connect();
		}

		$counters = getCounters();
		$newCount = $counters[$counterToUpdate] + 1;

		$query = "UPDATE `counters` SET `count`=$newCount WHERE `counter_name`='$counterToUpdate'";

		if (!mysql_query($query)) {
			$msg = 'Could not update counter: ' . $counterToUpdate . '. Error: ' . mysql_error();
			return $msg;
		} else {
			return $newCount;
		}
	}
?>