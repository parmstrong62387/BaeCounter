<?php

include '../dbconn.php';
include '../rss-utils.php';

class RssItem {

    public $href;
    public $title;
    public $year;
    public $month;
    public $day;

    private $pageInfo;

    public function __construct($href, $title, $year, $month, $day) 
    {
        $this->href = $href;
        $this->title = $title;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function getFullPubDate() {
        if (!isset($this->pageInfo)) {
            $this->pageInfo = getPageInfo($this->href);
        }

        return date_create($this->pageInfo[pub_date]);
    }

    public function getPageTitle() {
        if (!isset($this->pageInfo)) {
            $this->pageInfo = getPageInfo($this->href);
        }

        return $this->pageInfo[title];
    }

}

function sortRssItemsByPubDate($a, $b) {
    $yearCmp = strcmp($a->year, $b->year);
    if ($yearCmp != 0) {
        return -1 * $yearCmp;
    }
    $monthCmp = strcmp($a->month, $b->month);
    if ($monthCmp != 0) {
        return -1 * $monthCmp;
    }

    return -1 * strcmp($a->day, $b->day);
}

function sortRssItemsByFullPubDate($a, $b) {
    $fullA = $a->getFullPubDate();
    $fullB = $b->getFullPubDate();

    if ($fullA === false || $fullB === false) {
        return sortRssItemsByPubDate($a, $b);
    }

    if ($fullA == $fullB) {
       return 0;
    }

    return -1 * ($fullA < $fullB ? -1 : 1);
}

function getPageInfo($url) {
    $debug = false;

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

try {

    # Use the Curl extension to query Google and get back a page of results
    $url = "http://www.xinhuanet.com/english/home.htm";
    $html = get_web_page($url);

    #echo get_web_page($url);
    #echo file_get_contents($url);

    $addedLinks = Array();
    $rssItems = Array();

    # Create a DOM parser object
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $rss = new SimpleXMLExtended('<rss version="2.0"/>');
    $channel = $rss->addChild('channel');
    $channel->addChild('title', 'XINHUANEWS');
    $channel->addChild('description', 'China');
    $channel->addChild('generator', 'XINHUA NEWS');

    $pattern = "/.*\/(\d{4})-(\d{2})\/(\d{2}).*/";

    # Iterate over all the <a> tags
    foreach($dom->getElementsByTagName('a') as $link) {
        $href = $link->getAttribute('href');
        $title = $link->nodeValue;

        if (strpos($href, 'news.xinhuanet.com') !== false && !in_array($href, $addedLinks)) {

            array_push($addedLinks, $href);

            if (preg_match($pattern, $href, $matches, PREG_OFFSET_CAPTURE)) {
                $year = $matches[1][0];
                $month = $matches[2][0];
                $day = $matches[3][0];
                
                array_push($rssItems, new RssItem($href, $title, $year, $month, $day));
            } else {
                array_push($rssItems, new RssItem($href, $title, "", "", ""));
            }
        }
    }

    usort($rssItems, sortRssItemsByFullPubDate);

    for ($i = 0; $i < count($rssItems); $i++) {
        $item = $channel->addChild('item');
        $rssItem = $rssItems[$i];

        if (strlen(trim($rssItem->title)) > 0) {
            $item->addChildWithCDATA('title', $rssItem->title);
        } else {
            $item->addChildWithCDATA('title', $rssItem->getPageTitle());
        }
        $item->addChild('description', 'Hi baee');
        $item->addChild('link', $rssItem->href);

        if ($rssItem->getFullPubDate() !== false) {
            $item->addChild('pubDate', date_format($rssItem->getFullPubDate(), 'c'));
        } else if (strlen($rssItem->year) > 0) {
            $item->addChild('pubDate', $rssItem->year . '-' . $rssItem->month . '-' . $rssItem->day);
        } else {
            unset($item);
        }
    }

    $XML = $rss->asXML();
    $XML = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $XML);
    Header('Content-type: application/xml');
    print($XML);

} catch (Exception $e) {
    Header('Content-type: text/plain');
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>