<?php

include '../dbconn.php';
include '../rss-utils.php';

class RssItem {

    public $href;
    public $title;
    public $year;
    public $month;
    public $day;

    public $fullPubDate;

    public function __construct($href, $title, $year, $month, $day) 
    {
        $this->href = $href;
        $this->title = $title;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

}

function sortRssItems($a, $b) {
    $yearCmp = strcmp($a->year, $b->year);
    if ($yearCmp != 0) {
        return $yearCmp;
    }
    $monthCmp = strcmp($a->month, $b->month);
    if ($monthCmp != 0) {
        return $monthCmp;
    }
    $dayCmp = strcmp($a->day, $b->day);
    if ($dayCmp != 0) {
        return $dayCmp;
    }
}

Header('Content-type: text/plain');

function getPageInfo($url) {
    $debug = true;

    //First, try to get the pub date from the database
    $pageInfo = getPageInfoFromDB($url);
    if ($pageInfo !== false) {
        if ($debug) {
            echo "Found page in DB";
            echo "\n";
            echo $pageInfo[pub_date];
            echo "\n";
            echo $pageInfo[title];
            echo "\n";
        }

        return $pageInfo;
    }

    $metaDateFormat = "Y-m-d\TH:i:sT";
    $spanDateFormat = "Y-m-d H:i:sT";
    $response = get_web_page($url);
    $title = "";
    $date = false;
    if ($response !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXpath($dom);
        
        $title = $xpath->query("//title")[0]->nodeValue;
        $metaElement = $xpath->query("*/meta[@name='pubdate']");
        $spanElement = $xpath->query("//span[@id='pubtime']");

        if (!is_null($metaElement) && $metaElement->length > 0) {
            $dateStr = trim($metaElement[0]->getAttribute("content"));

            if ($debug) {
                echo "From meta";
                echo "\n";
                echo $dateStr;
                echo "\n";
            }

            $date = date_create_from_format($metaDateFormat, $dateStr);
        } else if (!is_null($spanElement) && $spanElement->length > 0) {
            $dateStr = trim($spanElement[0]->nodeValue);
            
            if ($debug) {
                echo "From span";
                echo "\n";
                echo $dateStr;
                echo "\n";
            }

            $date = date_create_from_format($metaDateFormat, $dateStr);
            if ($date === false) {
                if (strpos($dateStr, 'Z') === false) {
                    $date = date_create_from_format($spanDateFormat, $dateStr . 'Z');
                } else {
                    $date = date_create_from_format($spanDateFormat, $dateStr);
                }
            }
        }
    }

    $pageInfo = Array();
    $pageInfo[href] = $url;
    $pageInfo[pub_date] = date_format($date, 'c');
    $pageInfo[title] = htmlspecialchars(trim($title));

    //Add the new page to the DB to cache its information
    if ($date !== false) {
        addPageInfoToDB($pageInfo);
    }

    return $pageInfo;
}

function printDate($date) {
    if ($date === false) {
        echo "Fail";
    } else {
        echo date_format($date, 'c');
    }
    echo "\n\n";
}

$urlWithMeta = "http://news.xinhuanet.com/english/2016-10/14/c_135754769.htm";
$urlWithSpan = "http://news.xinhuanet.com/english/2016-10/14/c_135755015.htm";
$urlWithSpanTwo = "http://news.xinhuanet.com/english/2016-10/16/c_135756947.htm";

printDate(date_create(getPageInfo($urlWithMeta)[pub_date]));
printDate(date_create(getPageInfo($urlWithSpan)[pub_date]));
printDate(date_create(getPageInfo($urlWithSpanTwo)[pub_date]));

?>