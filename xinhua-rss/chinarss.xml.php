<?php

function get_web_page($url)
{
        //echo "curl:url<pre>".$url."</pre><BR>";
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
        echo "CURL:".$errmsg."<BR>";
    }
    return $content;
}

#Header('Content-type: text/plain');
Header('Content-type: text/xml');

# Use the Curl extension to query Google and get back a page of results
$url = "http://www.xinhuanet.com/english/home.htm";
$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
#curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36');
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
#curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
#curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$html = curl_exec($ch);
curl_close($ch);

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

# Iterate over all the <a> tags
foreach($dom->getElementsByTagName('a') as $link) {
    $href = $link->getAttribute('href');
    $title = $link->nodeValue;

    if (strpos($href, 'news.xinhuanet.com') !== false && !in_array($addedLinks, $href)) {
    	array_push($addedLinks, $href);
    	
    	$item = $channel->addChild('item');
    	$item->addChild('title', htmlspecialchars($title));
        $item->addChild('description', 'Hi baee');
    	$item->addChild('link', $href);

        if (preg_match($pattern, $href, $matches, PREG_OFFSET_CAPTURE)) {
            $item->addChild('pubDate', $matches[1][0] . '-' . $matches[2][0] . '-' . $matches[3][0]);
            #$item->addChild('pubDate', print_r($matches));
        }
    }
}

print($rss->asXML());

?>