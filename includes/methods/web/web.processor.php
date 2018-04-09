<?php
// Crawl XML feed and parse productions
$source = $url;

$productions = array();

function getWebPage($url){
        $options = array( 
	    	CURLOPT_RETURNTRANSFER => true, // to return web page
            CURLOPT_HEADER         => true, // to return headers in addition to content
            CURLOPT_FOLLOWLOCATION => true, // to follow redirects
            CURLOPT_ENCODING       => "",   // to handle all encodings
            CURLOPT_AUTOREFERER    => true, // to set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,  // set a timeout on connect
            CURLOPT_TIMEOUT        => 120,  // set a timeout on response
            CURLOPT_MAXREDIRS      => 10,   // to stop after 10 redirects
            CURLINFO_HEADER_OUT    => true, // no header out
            CURLOPT_SSL_VERIFYPEER => false,// to disable SSL Cert checks
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        );

        $handle = curl_init( $url );
        curl_setopt_array( $handle, $options );
 
    	// additional for storing cookie 
        $tmpfname = dirname(__FILE__).'/cookie.txt';
        curl_setopt($handle, CURLOPT_COOKIEJAR, $tmpfname);
        curl_setopt($handle, CURLOPT_COOKIEFILE, $tmpfname);

        $raw_content = curl_exec( $handle );
        $err = curl_errno( $handle );
        $errmsg = curl_error( $handle );
        $header = curl_getinfo( $handle ); 
        curl_close( $handle );
 
        $header_content = substr($raw_content, 0, $header['header_size']);
        $body_content = trim(str_replace($header_content, '', $raw_content));
    
    	// extract cookie from raw content for the viewing purpose         
        $cookiepattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m"; 
        preg_match_all($cookiepattern, $header_content, $matches); 
        $cookiesOut = implode("; ", $matches['cookie']);

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['headers']  = $header_content;
        $header['content'] = $body_content;
        $header['cookies'] = $cookiesOut;
    	return $header['content'];
}


// Sanitize & trim string
function trimString($string) {
	$string = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $string)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	return str_replace(array("&#39;", "/"),array("'","-"), $string);
}


//Get date format from string
function dateFromString($string) {
	
	//Convert string to a date string
	$string = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $string)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	$splittedDate = preg_split("(t/m|&)", $string);
	$date = $splittedDate[count($splittedDate)-1];
	$date = preg_replace("/(ma|di|wo|do|vr|za|zo)/i", "", $date);
	$date = str_ireplace(array("mrt","mei","okt", "v.a.", "uur", "."), array("mar","may","oct", "", "", ":"), $date);
	
	//Convert string to date format
	$dateArray = explode(' ', trim($date));
	
	//Define the year	
	if (strtotime((date('d')+1).' '.ucfirst($dateArray[1]).' '.date('Y')) < time()) {
		$year = date('Y')+1;
	} else {
		$year = date('Y');
	}
	
	//Convert date to timestamp
	if (strpos($date, ':') !== false) {
		$time = strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.$year.' '.$dateArray[2]);
	} else {
		$time = strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.$year);
	}
	
	return $time;

}

//Sanitize genre array
function listGenres($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, trim(filter_var(trim(strtolower($genreItem)), FILTER_SANITIZE_STRING)));
	}
	return $genreArr;
}


// Include simple_html_dom module to scrape a web page
include('includes/methods/web/simple_html_dom.php');


//Scrape performer names from web page
//include('includes/methods/web/performers.processor.php');


// Split tags to array element
$tags = explode('; ', $tags);
foreach ($tags as $key => $value) {
	$temp = explode(',', $value);
	$tags[$temp[0]] = str_replace('"', '', $temp[1]);
	unset($tags[$key]);

}

// XML scraper

$dom = new simple_html_dom(getWebPage($source));

foreach ($dom->find($tags['container'].' '.$tags['item']) as $production) {
	// Get last date of production
	$time = dateFromString($production->find($tags['date'], 0)->plaintext);	
	
	// Filter by month
	if (date('Y-m', $time) == $month) {
		//Check if genre exist
			$productionObj = new stdClass();
			
			// Put data to object
			$productionObj->title = trimString($production->find($tags['title'], 0)->plaintext);
			$productionObj->subtitle = trimString($production->find($tags['subtitle'], 0)->plaintext);
			$productionObj->venue = $location['venue'];
			$productionObj->location = $location['city'];
			$productionObj->genre = listGenres($production->find($tags['genre']));
			if (count($productionObj->genre) <= 1) {
				$productionObj->genre[0] = 'voorstelling';
			}
		
			if (strpos($production->find('a', 0)->href, $tags['baseUrl']) !== false) {
				$productionObj->link = filter_var(trim($production->find('a', 0)->href), FILTER_SANITIZE_URL);
			} else {
				$productionObj->link = filter_var(trim($tags['baseUrl'].$production->find('a', 0)->href), FILTER_SANITIZE_URL);
			}
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
			//$productionObj->performers = getPerformers($productionObj->link);
		
		
			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			if (stripos($exclude, $productionObj->genre[0]) === false && stripos($exclude, $productionObj->genre[1]) === false && stripos($exclude, $productionObj->title) === false && stripos($productionObj->title, 'inleiding') === false && stripos($lastShow->status, "uitverkocht") === false && $lastShow->status !== "Geannuleerd") {
				array_push($productions, $productionObj);
			}
		}

}
?>