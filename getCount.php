<?php

include 'dbconn.php';

$counterToUpdate = $_GET['counter'];
$counters = getCounters();

echo $counters[$counterToUpdate];

?>