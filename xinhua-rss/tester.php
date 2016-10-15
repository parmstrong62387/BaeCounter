<?php

include '../dbconn.php';

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

function get_web_page($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 15,      // timeout on connect
        CURLOPT_TIMEOUT        => 15,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects

    );

    $ch      = curl_init($url);
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch,CURLINFO_EFFECTIVE_URL );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;

    //change errmsg here to errno
    if ($errmsg)
    {
        return false;
    }

    return $content;
}

function getFullPubDate($url) {
    $debug = true;

    //First, try to get the pub date from the database
    $dbPubDate = getPubDateFromDB($url);
    if ($dbPubDate !== false) {
        if ($debug) {
            echo "Found url in DB";
            echo "\n";
            echo $dbPubDate;
            echo "\n";
        }

        return $dbPubDate;
    }

    $metaDateFormat = "Y-m-d\TH:i:sT";
    $spanDateFormat = "Y-m-d H:i:sT";
    $response = get_web_page($url);
    $date = false;
    if ($response !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXpath($dom);
        
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

    //Add the new date to the DB to cache it with the associated URL
    if ($date !== false) {
        addPubDateToDB($url, date_format($date, 'c'));
    }

    return $date;
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

printDate(getFullPubDate($urlWithMeta));
printDate(getFullPubDate($urlWithSpan));
printDate(getFullPubDate($urlWithSpanTwo));

?>