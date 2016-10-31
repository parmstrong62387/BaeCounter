<?php

include '../dbconn.php';
include '../rss-utils-refactor.php';

class XinhuaRssItem extends RssItem {

    protected function getTitleFromDom($xpath) {
        return $xpath->query("//title")[0]->nodeValue;
    }

    protected function getDateFromDom($xpath, $debug) {
        $metaDateFormat = "Y-m-d\TH:i:sT";
        $spanDateFormat = "Y-m-d H:i:sT";

        $date = false;
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

        return $date;
    }

}

function sortRssItemsByFullPubDate($a, $b) {
    $fullA = $a->getFullPubDate();
    $fullB = $b->getFullPubDate();

    if ($fullA == $fullB) {
       return 0;
    }

    return -1 * ($fullA < $fullB ? -1 : 1);
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
                
                array_push($rssItems, new XinhuaRssItem($href, $title, $year, $month, $day));
            } else {
                array_push($rssItems, new XinhuaRssItem($href, $title, "", "", ""));
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