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

?>