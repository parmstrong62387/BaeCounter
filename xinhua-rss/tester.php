<?php

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
    $metaDateFormat = "Y-m-d\TH:i:sT";
    $response = get_web_page($url);
    if ($response !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXpath($dom);
        
        $metaElement = $xpath->query("*/meta[@name='pubdate']/@content");
        $spanElement = $xpath->query("*/span[@id='pubtime']");
        
        // $elements = $xpath->query("*/div[@id='yourTagIdHere']");

		if (!is_null($metaElement) && count($metaElement) > 0) {
			$dateStr = trim($metaElement[0]->nodeValue);
            $date = date_create_from_format($metaDateFormat, $dateStr);
            
            echo $dateStr;
            echo "\n";

            if ($date !== false) {
                echo date_format($date, 'c');
            } else {
                echo "couldn't parse date";
            }

            echo "\n";
		} else if (!is_null($spanElement) && count($spanElement) > 0) {
			$dateStr = trim($spanElement[0]->nodeValue);
            $date = date_create_from_format($metaDateFormat, $dateStr);
            
            echo $dateStr;
            echo "\n";

            if ($date !== false) {
                echo date_format($date, 'c');
            } else {
                echo "couldn't parse date";
            }

            echo "\n";
		}
    }

    return false;
}

$urlWithMeta = "http://news.xinhuanet.com/english/2016-10/14/c_135754769.htm";
$urlWithSpan = "http://news.xinhuanet.com/english/2016-10/14/c_135755015.htm";

getFullPubDate($urlWithMeta);
echo "\n";
getFullPubDate($urlWithSpan);

echo "\n";

$rssItems = Array();
array_push($rssItems, new RssItem("z test", "z test 2", "z test 3", "z test 4", "z test 5"));
array_push($rssItems, new RssItem("test", "test 2", "test 3", "test 4", "test 5"));
usort($rssItems, sortRssItems);
echo $rssItems[0]->href;
echo "\n";
echo $rssItems[1]->href;

?>