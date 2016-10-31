<?php

require_once('../google_search.php');
require_once('../dbconn.php');
require_once('../rss-utils-temp.php');

$url = 'http://www.cnn.com/2016/10/04/asia/china-rubiks-cube/';
$response = get_web_page($url);
Header('Content-type: text/plain');
echo $response;

if ($response !== false) {
    // $dom = new DOMDocument();
    // @$dom->loadHTML($response);
    // $xpath = new DOMXpath($dom);
    // getDateFromDom($xpath, true);
} else {
	echo 'Could not load page.';
}

function getDateFromDom($xpath, $debug) {
    $date = false;
    $pubDate = $xpath->query("*/meta[@name='pubdate']");
    $dateIssued = $xpath->query("*/meta[@name='DC.date.issued']");

    if (!is_null($pubDate) && $pubDate->length > 0) {
        $dateStr = trim($pubDate[0]->getAttribute("content"));
        if (strpos($dateStr, 'Z') === false) {
            $dateStr = $dateStr . 'Z';
        }
        if ($debug) {
    		echo "Using pub date: " . $dateStr;
    	}
        $date = date_create($dateStr);
    } else if (!is_null($dateIssued) && $dateIssued->length > 0) {
        $dateStr = trim($dateIssued[0]->getAttribute("content"));
        if (strpos($dateStr, 'Z') === false) {
            $dateStr = $dateStr . 'Z';
        }
        if ($debug) {
    		echo "Using date issued: " . $dateStr;
    	}
        $date = date_create($dateStr);
    } else {
    	if ($debug) {
    		echo "No usable date found";
    	}
    }

    return $date;
}

?>