<?php

include '../rss-utils.php';

$rss = new SimpleXMLExtended('<rss version="2.0"/>');
$channel = $rss->addChild('channel');
$channel->addChild('title', 'Feed of my baee\'s stories');
$channel->addChild('description', 'My baee writes all kinds of great stories. I want to stay up to date with them.');
$channel->addChild('generator', 'Bae');

$searchUrl = "http://searchapp.cnn.com/search/query.jsp?page=1&npp=1000&start=1&text=%2522Serenitie%2BWang%2522&type=all&bucket=true&sort=date&csiID=csi2&collection=STORIES";
$html = get_web_page($searchUrl);
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXpath($dom);    
$textArea = $xpath->query("//textarea[@id='jsCode']");

function parseDate($dateStr) {
    return date_create_from_format("h:i A, M d, Y", $dateStr);
}

function formatDate($dateStr) {
    $date = parseDate($dateStr);
    if ($date === false) {
        return "";
    }

    return date_format($date, 'c');
}

function sortResults($a, $b) {
    $dateA = parseDate($a["mediaDateUts"]);
    $dateB = parseDate($b["mediaDateUts"]);

    if ($dateA === false || $dateB === false) {
        return 0;
    }

    if ($dateA == $dateB) {
       return 0;
    }

    return -1 * ($dateA < $dateB ? -1 : 1);
}

if (!is_null($textArea) && $textArea->length > 0) {
    $json = json_decode($textArea[0]->nodeValue, true);
    
    $results = $json["results"][0];
    usort($results, sortResults);

    foreach ($results as $result) {
        $url = $result["url"];
        if (strpos($url, ".com") === false) {
            $url = "http://www.cnn.com" . $url;
        }
        $dateStr = formatDate($result["mediaDateUts"]);

        if (strlen($dateStr) > 0) {
            $item = $channel->addChild("item");
            $item->addChildWithCDATA("title", $result["title"]);
            $item->addChildWithCDATA("description", $result["description"]);
            $item->addChild("link", $url);
            $item->addChild("pubDate", $dateStr);
        }
    }
}

$XML = $rss->asXML();
$XML = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $XML);
Header('Content-type: application/xml');
print($XML);

?>