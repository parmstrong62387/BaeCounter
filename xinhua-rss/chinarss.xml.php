<?php

/**
 * SimpleXMLExtended Class
 *
 * Extends the default PHP SimpleXMLElement class by 
 * allowing the addition of cdata
 *
 * @since 1.0
 *
 * @param string $cdata_text
 */
class SimpleXMLExtended extends SimpleXMLElement
{
    public function addCData($cdata_text)
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
    /**
     * Adds a child with $value inside CDATA
     * @param unknown $name
     * @param unknown $value
     */
    public function addChildWithCDATA($name, $value = NULL)
    {
        $new_child = $this->addChild($name);
        if ($new_child !== NULL) {
            $node = dom_import_simplexml($new_child);
            $no   = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }
        return $new_child;
    }
}

class RssItem {

    public $href;
    public $title;
    public $year;
    public $month;
    public $day;

    private $fullPubDate;

    public function __construct($href, $title, $year, $month, $day) 
    {
        $this->href = $href;
        $this->title = $title;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function getFullPubDate() {
        if (!isset($this->fullPubDate)) {
            $this->fullPubDate = getFullPubDate($this->href);
        }

        return $this->fullPubDate;
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
    $debug = false;
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

    return $date;
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

        if (strpos($href, 'news.xinhuanet.com') !== false && !in_array($addedLinks, $href)) {

            if (preg_match($pattern, $href, $matches, PREG_OFFSET_CAPTURE)) {
                array_push($addedLinks, $href);
                $year = $matches[1][0];
                $month = $matches[2][0];
                $day = $matches[3][0];
                
                array_push($rssItems, new RssItem($href, $title, $year, $month, $day));
            }
        }
    }

    usort($rssItems, sortRssItemsByPubDate);
    $rssItems = array_slice($rssItems, 0, 30);
    usort($rssItems, sortRssItemsByFullPubDate);

    for ($i = 0; $i < 30 && $i < count($rssItems); $i++) {
        $item = $channel->addChild('item');
        $rssItem = $rssItems[$i];

        $item->addChildWithCDATA('title', $rssItem->title);
        $item->addChild('description', 'Hi baee');
        $item->addChild('link', $rssItem->href);

        if ($rssItem->getFullPubDate() !== false) {
            $item->addChild('pubDate', date_format($rssItem->getFullPubDate(), 'c'));
        } else {
            $item->addChild('pubDate', $rssItem->year . '-' . $rssItem->month . '-' . $rssItem->day);
        }
    }


    // $item->addChild('pubDate', $matches[1][0] . '-' . $matches[2][0] . '-' . $matches[3][0]);

    // $fullPubDate = getFullPubDate($href);
    //         if ($fullPubDate !== false) {
    //             $item->addChild('pubDate', date_format($fullPubDate, 'c'));
    //         } 

    $XML = $rss->asXML();
    $XML = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $XML);
    Header('Content-type: application/xml');
    print($XML);

} catch (Exception $e) {
    Header('Content-type: text/plain');
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>