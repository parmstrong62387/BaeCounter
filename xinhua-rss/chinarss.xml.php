<?php

try {

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

            if (!is_null($metaElement) && count($metaElement) > 0) {
                $dateStr = trim($metaElement[0]->nodeValue);
                return date_create_from_format($metaDateFormat, $dateStr);
            } else if (!is_null($spanElement) && count($spanElement) > 0) {
                $dateStr = trim($spanElement[0]->nodeValue);
                return date_create_from_format($metaDateFormat, $dateStr);
            }
        }

        return false;
    }

    # Use the Curl extension to query Google and get back a page of results
    $url = "http://www.xinhuanet.com/english/home.htm";
    $html = get_web_page($url);

    #echo get_web_page($url);
    #echo file_get_contents($url);

    $addedLinks = Array();

    # Create a DOM parser object
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $rss = new SimpleXMLElement('<rss version="2.0"/>');
    $channel = $rss->addChild('channel');
    $channel->addChild('title', 'XINHUANEWS');
    $channel->addChild('description', 'China');
    $channel->addChild('generator', 'XINHUA NEWS');

    $pattern = "/.*\/(\d{4})-(\d{2})\/(\d{2}).*/";

    $i = 0;

    # Iterate over all the <a> tags
    foreach($dom->getElementsByTagName('a') as $link) {
        $href = $link->getAttribute('href');
        $title = $link->nodeValue;

        if (strpos($href, 'news.xinhuanet.com') !== false && !in_array($addedLinks, $href)) {

            $i++;
            if ($i >= 10) {
                break;
            }

        	array_push($addedLinks, $href);
        	
        	$item = $channel->addChild('item');
        	$item->addChild('title', htmlspecialchars($title));
            $item->addChild('description', 'Hi baee');
        	$item->addChild('link', $href);

            $fullPubDate = getFullPubDate($href);
            if ($fullPubDate !== false) {
                $item->addChild('pubDate', date_format($fullPubDate, 'c'));
            } else if (preg_match($pattern, $href, $matches, PREG_OFFSET_CAPTURE)) {
                $item->addChild('pubDate', $matches[1][0] . '-' . $matches[2][0] . '-' . $matches[3][0]);
            }
        }
    }

    Header('Content-type: text/xml');
    print($rss->asXML());

} catch (Exception $e) {

    Header('Content-type: text/plain');
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}

?>