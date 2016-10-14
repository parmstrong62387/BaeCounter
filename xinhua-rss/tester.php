<?php

Header('Content-type: text/plain');

$metaDateFormat = "Y-m-d\\Th:i:sT";

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
    $response = get_web_page($url);
    if ($response !== false) {
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $xpath = new DOMXpath($dom);
        
        $metaElement = $xpath->query("*/meta[@name='pubdate']/@content");
        $spanElement = $xpath->query("*/span[@id='pubtime']");
        
        // $elements = $xpath->query("*/div[@id='yourTagIdHere']");

		if (!is_null($metaElement) && count($metaElement) > 0) {
			echo $metaElement[0]->nodeValue;
		} else if (!is_null($spanElement) && count($spanElement) > 0) {
			echo $spanElement[0]->nodeValue;
		}
    }

    return false;
}

$urlWithMeta = "http://news.xinhuanet.com/english/2016-10/14/c_135754769.htm";
$urlWithSpan = "http://news.xinhuanet.com/english/2016-10/14/c_135755015.htm";

getFullPubDate($urlWithMeta);
echo "\n";
getFullPubDate($urlWithSpan);

?>