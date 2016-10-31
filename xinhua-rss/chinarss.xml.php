<?php

include '../dbconn.php';
include '../rss-utils.php';

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

class XinhuaRssFeed extends RssFeed {

    protected function constructRssItem($href, $title) {
        return new XinhuaRssItem($href, $title);
    }

    protected function getLinksFromDom($dom) {
        return $dom->getElementsByTagName('a');
    }

    protected function shouldInclude($href) {
        return strpos($href, 'news.xinhuanet.com') !== false;
    }

}

try {

    $feed = new XinhuaRssFeed("http://www.xinhuanet.com/english/home.htm",
        "XINHUANEWS",
        "China",
        "XINHUA NEWS");

    $feed->printFeed();

} catch (Exception $e) {
    Header('Content-type: text/plain');
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>