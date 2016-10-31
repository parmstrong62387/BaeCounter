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

abstract class RssItem {

    public $href;
    public $title;

    private $pageInfo;

    public function __construct($href, $title) 
    {
        $this->href = $href;
        $this->title = $title;
    }

    public function getFullPubDate() {
        if (!isset($this->pageInfo)) {
            $this->pageInfo = $this->getPageInfo($this->href);
        }

        return date_create($this->pageInfo[pub_date]);
    }

    public function getPageTitle() {
        if (!isset($this->pageInfo)) {
            $this->pageInfo = $this->getPageInfo($this->href);
        }

        return $this->pageInfo[title];
    }

    private function getPageInfo($url) {
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

        $response = get_web_page($url);
        $title = "";
        $date = false;
        if ($response !== false) {
            $dom = new DOMDocument();
            @$dom->loadHTML($response);
            $xpath = new DOMXpath($dom);
            
            $title = $this->getTitleFromDom($xpath);
            $date = $this->getDateFromDom($xpath);
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

    protected abstract function getTitleFromDom($xpath);

    protected abstract function getDateFromDom($xpath, $debug);

}

abstract class RssFeed {

    protected $feedUrl;
    protected $feedTitle;
    protected $feedDescription;
    protected $feedGenerator;
    protected $rssItems;
    protected $addedLinks;
    protected $feedLoaded;

    public function __construct($feedUrl, $feedTitle, $feedDescription, $feedGenerator) {
        $this->feedUrl = $feedUrl;
        $this->feedTitle = $feedTitle;
        $this->feedDescription = $feedDescription;
        $this->feedGenerator = $feedGenerator;
        $this->rssItems = Array();
        $this->addedLinks = Array();
        $this->feedLoaded = false;
    }

    public function printFeed() {
        if (!$this->feedLoaded) {
            $this->loadFeed();
        }

        $rss = new SimpleXMLExtended('<rss version="2.0"/>');
        $channel = $rss->addChild('channel');
        $channel->addChild('title', $this->feedTitle);
        $channel->addChild('description', $this->feedDescription);
        $channel->addChild('generator', $this->feedGenerator);

        for ($i = 0; $i < count($this->rssItems); $i++) {
            $item = $channel->addChild('item');
            $rssItem = $this->rssItems[$i];

            if (strlen(trim($rssItem->title)) > 0) {
                $item->addChildWithCDATA('title', $rssItem->title);
            } else {
                $item->addChildWithCDATA('title', $rssItem->getPageTitle());
            }
            $item->addChild('description', 'Hi baee');
            $item->addChild('link', $rssItem->href);

            if ($rssItem->getFullPubDate() !== false) {
                $item->addChild('pubDate', date_format($rssItem->getFullPubDate(), 'c'));
            } else {
                unset($item);
            }
        }

        $XML = $rss->asXML();
        $XML = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $XML);
        Header('Content-type: application/xml');
        print($XML);
    }

    public function loadFeed() {
        foreach($this->getLinks() as $link) {
            $href = $link['link'];
            $title = $link['title'];

            if (!in_array($href, $this->addedLinks) && $this->shouldInclude($href)) {
                array_push($this->addedLinks, $href);
                array_push($this->rssItems, $this->constructRssItem($href, $title));
            }
        }

        usort($this->rssItems, sortRssItemsByFullPubDate);

        $this->feedLoaded = true;
    }

    protected abstract function constructRssItem($href, $title);

    protected abstract function getLinks();

    protected abstract function shouldInclude($link);

}

function sortRssItemsByFullPubDate($a, $b) {
    $fullA = $a->getFullPubDate();
    $fullB = $b->getFullPubDate();

    if ($fullA == $fullB) {
       return 0;
    }

    if ($fullA === false) {
        return 1;
    } else if ($fullB === false) {
        return -1;
    }

    return -1 * ($fullA < $fullB ? -1 : 1);
}

function get_web_page($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        // CURLOPT_HEADER         => false,    // don't return headers
        // CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        // CURLOPT_ENCODING       => "",       // handle all encodings
        // CURLOPT_USERAGENT      => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36", // who am i
        // CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        // CURLOPT_CONNECTTIMEOUT => 15,      // timeout on connect
        // CURLOPT_TIMEOUT        => 15,      // timeout on response
        // CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects

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

?>