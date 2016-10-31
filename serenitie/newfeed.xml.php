<?php

require_once('../google_search.php');
require_once('../dbconn.php');
require_once('../rss-utils-temp.php');

class CnnRssItem extends RssItem {

    protected function getTitleFromDom($xpath) {
        return $xpath->query("//title")[0]->nodeValue;
    }

    protected function getDateFromDom($xpath, $debug) {
        $date = false;
        $pubDate = $xpath->query("*/meta[@name='pubdate']");
        $dateIssued = $xpath->query("*/meta[@name='DC.date.issued']");

        if (!is_null($pubDate) && $pubDate->length > 0) {
            $dateStr = trim($pubDate[0]->getAttribute("content"));
            if (strpos($dateStr, 'Z') === false) {
                $dateStr = $dateStr . 'Z';
            }
            $date = date_create($dateStr);
        } else if (!is_null($dateIssued) && $dateIssued->length > 0) {
            $dateStr = trim($dateIssued[0]->getAttribute("content"));
            if (strpos($dateStr, 'Z') === false) {
                $dateStr = $dateStr . 'Z';
            }
            $date = date_create_from_format($metaDateFormat, $dateStr);
        }

        return $date;
    }

}

class CnnRssFeed extends RssFeed {

    protected function constructRssItem($href, $title) {
        return new CnnRssItem($href, $title);
    }

    protected function getLinks() {
        $googleSearch = new GoogleSearch($this->feedUrl);
        return $googleSearch->getResults();
    }

    protected function shouldInclude($href) {
        return true;
    }

}

try {

    $feedUrl = "https://www.google.com/search?q=%22Serenitie%2BWang%22+-cnnespanol+site:cnn.com&safe=active&tbs=qdr:m,sbd:1&num=100";
    $feed = new CnnRssFeed($feedUrl,
        "Baee Feed",
        "A feed of baee's stories",
        "CNN - Baee");

    $feed->loadFeed();

    $feed->printFeed();

} catch (Exception $e) {
    Header('Content-type: text/plain');
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>