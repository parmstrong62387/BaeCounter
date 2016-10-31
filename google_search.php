<?php
require_once('simple_html_dom.php');

class GoogleSearch {
    private $searchUrl;
    private $results;

    public function __construct($searchUrl) {
        $this->searchUrl = $searchUrl;
        $this->results = Array();
        $this->load();
    }

    public function getResults() {
        return $this->results;
    }

    private function load() {
        $html = file_get_html($this->searchUrl);
        // echo $html;
        // echo "hello world";
        $linkObjs = $html->find('h3.r a');
        foreach ($linkObjs as $linkObj) {
            $title = trim($linkObj->plaintext);
            $link  = trim($linkObj->href);
            
            // if it is not a direct link but url reference found inside it, then extract
            if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
                $link = $matches[1];
            } else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
                continue;    
            }
            
            $linkArr = Array();
            $linkArr['title'] = $title;
            $linkArr['link'] = $link;
            array_push($this->results, $linkArr);
        }
    }

}
?>