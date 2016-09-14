<?php

	include 'dbconn.php';

	$counterToUpdate = $_GET['counter'];

	if (isset($counterToUpdate)) {
		 echo updateCounter($counterToUpdate);
	}

?>