<?php
// Include simple_html_dom module to scrape a web page
header('Content-Type: application/json');
include('simple_html_dom.php');


//Sort performer array by value length for keyword insertion
function sortByLength($a,$b) {
    return strlen($b)-strlen($a);
}

// Open web page like a client and store cookies
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

function getPerformers($url, $tag) {
	//Create return object to store performers
	$performerObj = new stdClass();
	$performerObj->success = false;
	
	//Convert content on page to plain text
	$dom = new simple_html_dom(getWebPage($url));
	//Sanitize and remove unnecessary characters like tabs and html tags from string. Convert string to utf-8 format.
	$content = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $dom->plaintext)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	$content = str_replace(array(' |', '-'), array(':', '_'), $content);

	if (strlen($content)>1) {
		
		//Check if predefined performers tag exists and if it only contains words/names
		if (count($tag) > 0 && strlen($tag['performers']) > 1) {
			$performers = array();
			$credits = preg_split("/(\w*: )/", $dom->find($tag['performers'], -1)->plaintext);
			$credits = implode(', ', $credits);
			
			$credits = explode(', ', preg_replace("/\s+/", " ", $credits));
			
			//Only remain names
			foreach ($credits as $ckey => $credit) {
				if (preg_match('#[0-9]#', $credit)){
					unset($credits[$ckey]);
				} else {
					$tempstr = explode(' ', trim($credit));
					//Empty array element
					$credits[$ckey] = '';
					
					//Add relevant string elements as performer name
					foreach ($tempstr as $tkey => $strelem) {
						if ($tkey == 0) {
							$credits[$ckey] .= $strelem;
						}
						if ($tkey == 1) {
							$credits[$ckey] .= ' '.$strelem;
						}
						if ($tkey == 2 && ($tempstr[1] == 'van' || $tempstr[1] == 'ten' || $tempstr[1] == 'de' || $tempstr[1] == 'den')) {
							$credits[$ckey] .= ' '.$strelem;
						}
						if ($tkey == 3 && ($tempstr[1] == 'van' && $tempstr[2] == 'der')) {
							$credits[$ckey] .= ' '.$strelem;
						}
					}
					if (strlen($credit) == 0 || count($tempstr) <= 1) {
						unset($credits[$ckey]);
					}
				}
			}
			if (count($credits)>1) {
				$performers = $credits;
			}
		} else {
			//Find words with a colon (e.g. regie:) and add it with its delimiter to the array
			$credits = preg_split("/(\w*: )/", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
			$credits = array_chunk(array_slice($credits, 1, -1, true), 2, false);

			$performers = array();

			$replace = array('e.a.', 'e.v.a.');
			$replacement = array('', '');

			$excludePattern = "muzikale leiding";
			$rolePattern = "spelers,spel,regie,eindregie,tekst,compositie,met,musici,muzikale leiding,muziek,muzikant,muzikanten";
			$filteredRoles = "";


			//Loop each role
			foreach($credits as $role) {
				$role[0] = str_replace(':','', $role[0]);
				if (strlen(trim($role[0])) > 2) {
					if (stripos($rolePattern, trim($role[0]), 0) !== false && stripos($filteredRoles, trim($role[0]), 0) === false) {
						//Add role to 'filteredRoles' string
						$filteredRoles .=  trim($role[0]).',';
						//Loop each performer, add them to array
						$performer = preg_split('/(,| en | i.s.m.)+/i', $role[1]);
						foreach($performer as $val) {
							//Check if string isn't empty and doesn't include any words or characters that need to be excluded
							if (strlen($val)>1 && stripos($excludePattern, trim($val)) === false && stripos($val, '#', 0) === false) {
								array_push($performers, str_replace($replace, $replacement, trim($val)));
							}
						}
					}
				}
			}
		}
		
		if (count($performers) >= 1) {
			$performers = array_unique($performers, SORT_STRING);
			//Sort performers by length for keyword insertion
			usort($performers,'sortByLength');
			$performerObj->success = true;
			$performerObj->performers = $performers;
		}
			
		
	}
	
	if ($performerObj->success == true) {
		return $performerObj->performers;
	} else {
		return false;
	}
}
//print_r(getPerformers("https://www.bimhuis.nl/agenda/tommy-got-waxed/", array("performers"=>".intro")));
?>