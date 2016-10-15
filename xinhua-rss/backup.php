<?php

$metaDateFormat = "Y-m-d\\Th:i:sT";

function get_web_page($url)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $html = curl_exec($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);

    //change errmsg here to errno
    if ($errmsg)
    {
        return false;
    }

    return $content;
}

function getFullPubDate($url) {
    $response = get_web_page($url);
    if ($response !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXpath($dom);
        
        $meta_pub_date = $xpath->query("*/meta[name='pubdate']");
        $span_pub_time = $xpath->query("*/span[id='pubtime']");
        if (!is_null($meta_pub_date) && count($meta_pub_date) > 0) {
            $rawDate = $meta_pub_date[0]->nodeValue;
            $return date_create_from_format($rawDate, $metaDateFormat);
        }
    }

    return false;
}

// class RssItem {

//     private $href;
//     private $title;
//     private $pubDate;
//     private $fullPubDate;

//     public function __construct($href, $title, $pubDate) 
//     {
//         $this->$href = $href;
//         $this->$title = $title;
//         $this->$pubDate = $pubDate;
//     }

// }

#Header('Content-type: text/plain');
Header('Content-type: text/xml');

$addedLinks = Array();

# Use the Curl extension to query Google and get back a page of results
$url = "http://www.xinhuanet.com/english/home.htm";
$html = get_web_page($url);

# Create a DOM parser object
$dom = new DOMDocument();
@$dom->loadHTML($html);

$rss = new SimpleXMLElement('<rss version="2.0"/>');
$channel = $rss->addChild('channel');
$channel->addChild('title', 'XINHUANEWS');
$channel->addChild('description', 'China');
$channel->addChild('generator', 'XINHUA NEWS');

$pattern = "/.*\/(\d{4})-(\d{2})\/(\d{2}).*/";

# Iterate over all the <a> tags
foreach($dom->getElementsByTagName('a') as $link) {
    $href = $link->getAttribute('href');
    $title = $link->nodeValue;

    if (strpos($href, 'news.xinhuanet.com') !== false && !in_array($addedLinks, $href)) {
    	array_push($addedLinks, $href);
    	
    	$item = $channel->addChild('item');
    	$item->addChild('title', htmlspecialchars($title));
        $item->addChild('description', 'Hi baee');
    	$item->addChild('link', $href);

        //$fullPubDate = getFullPubDate($href);
        if (false) {
            //$item->addChild('pubDate', date_format($fullPubDate, 'c'));
        } else if (preg_match($pattern, $href, $matches, PREG_OFFSET_CAPTURE)) {
            $item->addChild('pubDate', $matches[1][0] . '-' . $matches[2][0] . '-' . $matches[3][0]);
        }
    }
}

print($rss->asXML());

?>