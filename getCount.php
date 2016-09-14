<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include 'dbconn.php';

function getId($counters) {
	return $counters['patrick'] . ' ' . $counters['yingying'];
}

function outputData($counters) {
	echo 'id: ' . getId($counters) . "\n\n";
	echo 'data: ' . json_encode($counters) . "\n\n";
	ob_flush();
	flush();
}

$lastId = $_SERVER["HTTP_LAST_EVENT_ID"];
$counters = getCounters();
if (!isset($lastId) || getId($counters) != $lastId) {
	outputData($counters);
}

sleep(3);

?>