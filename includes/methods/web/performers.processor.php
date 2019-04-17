<?php
// Include simple_html_dom module to scrape a web page
header('Content-Type: application/json');
$performers = array();

include(dirname(__FILE__).'/simple_html_dom.php');

function getCredits($content) {
	//Find words with a colon (e.g. regie:) and add it with its delimiter to the array
	$credits = preg_split("/(\w*: )/", $content, -1, PREG_SPLIT_DELIM_CAPTURE);
	$credits = array_chunk(array_slice($credits, 1, -1, true), 2, false);
	
	$tempPerformers = array();

	$replace = array('e.a.', 'e.v.a.');
	$replacement = array('', '');

	$excludePattern = "muzikale leiding";
	$rolePattern = "spelers,spel,regie,regisseur,schrijver,schijver,eindregie,tekst,compositie,met,musici,muzikale leiding,muziek,muzikant,muzikanten";
	$filteredRoles = "";
	//Loop each role
	foreach($credits as $role) {
		$role[0] = str_replace(':','', $role[0]);
		if (strlen(trim($role[0])) > 2) {
			if (stripos($rolePattern, trim($role[0]), 0) !== false && stripos($filteredRoles, trim($role[0]), 0) === false) {
				$replace = array('e.a.', 'e.a', 'e.v.a.', 'met ',);
				$replacement = array('', '', '', '');
				
				$role[1] = str_ireplace($replace, $replacement, preg_replace("/\([^)]+\)/","",$role[1]));
				//Add role to 'filteredRoles' string
				$filteredRoles .=  trim($role[0]).',';
				//Loop each performer, add them to array
				$performer = preg_split('/(,| en | i.s.m.)+/i', $role[1]);
				
				$noList = true;
				//Check if performer roles exist in splitted values. If so, split new values on their performer role
				foreach($performer as $key => $combined) {
					//Loop each word of performer value and check if performer role exists in the value
					foreach(explode(' ', $combined) as $val) {
						if (stripos($rolePattern, $val.',') !== false && strlen($val) > 3) {
							$combined = trim(str_ireplace($val, ', ', $combined));
							$noList = false;
						} 
					}
					
					// If performers could be splitted, loop each splitted performer and check if it's a name
					if ($noList == false && strpos($combined, ',') !== false) {
						foreach(explode(', ', $combined) as $ckey => $val) {
							if (strlen($val) > 1) {
								$tempstr = explode(' ', trim($val));
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
								
								array_push($tempPerformers, trim($credits[$ckey]));
							}

						}
					}
					
					// If performer roles aren't listed in the performer value, add the full performer value to the performer list
					if ($noList == true) {
						array_push($tempPerformers, trim($combined));
					}
					
					
				}
				
				/*
				foreach($performer as $val) {
					//Check if string isn't empty and doesn't include any words or characters that need to be excluded
					if ((strlen($val)>1 && strlen($val)<1000) && stripos($excludePattern, trim($val)) === false && stripos($val, '#', 0) === false) {
						array_push($performers, str_replace($replace, $replacement, trim($val)));
					}
				}
				*/
			}
		}
	}

	if (count($tempPerformers) > 0) {
		return $tempPerformers;
	} else {
		return false;
	}
}


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

			//Check if multiple selectors are given in performers tag
			if (count(explode(' + ', $tag['performers'])) > 1) {
				foreach(explode(' + ', $tag['performers']) as $key => $tagString) {
					$credit = preg_split("/(\w*: )/", $dom->find($tagString, -1)->plaintext);
					// Select property by name and key
					if (preg_match_all("/\[([^\]]*)\]/", $tagString, $matches)) {
						//Remove brackets with key from string
						$tagString = str_replace($matches[0][0], '', $tagString);
						$credit = $dom->find($tagString, $matches[1][0])->plaintext;
						$credits .= $credit.', ';

					}

					//Determine if multiple lines or tabs have been used to list the performers
					if (preg_match("/(\s+ )/", $credit[0], $match)) {
						$credit = preg_split("/(\s+ )/", $dom->find($tagString, -1)->plaintext);
						foreach($credit as $ckey => $tempItem) {
							$credit[$ckey] = substr($tempItem, 0, strpos($tempItem, '&#8211'));
						}
						$credits = $credit;
					} else {
						if ($key > 0) {
							$credits .= ', '.implode(', ', $credit);
						} else {
							$credits .= implode(', ', $credit);
						}	
					}
				}

			} else {
				//Determine if performers are listed in separate html elements
				if (count($dom->find($tag['performers'])) > 1) {
					$ckey = 0;
					foreach($dom->find($tag['performers']) as $tempItem) {
						$subitems = $tempItem->find('a');
						// Determine if there are multiple performers given
						if (count($subitems) > 1) {
							foreach($subitems as $performerItem) {
								$credits[$ckey] = $performerItem->plaintext;
								$ckey++;
							}
						} else {
							$credits[$ckey] = trim(preg_split("/(,)/", $tempItem->plaintext)[0]);
						}
						$ckey++;

					}
				} else {

					 //$tempProgramme = explode(",", str_replace("\r\n", ",", $dom->find($tag['performers'], -1)->plaintext));
					 //$credits = $tempProgramme;


				    /* Added for Brussels Philharmonic */
				    /*
				    $tempProgramme = explode("\n", $dom->find($tag['performers'], -1)->plaintext);
				    foreach($tempProgramme as $key => $val) {
				    	$tempVal = explode('•', $val)[0];
				    	$tempProgramme[$key] = trim($tempVal);
				    }
				    $credits = $tempProgramme;

				    /* Removed for Brussels Philharmonic */

					$credit = preg_split("/(\w*: )/", preg_replace("/\s+/", " ", $dom->find($tag['performers'], -1)->plaintext));
					$credits .= implode(', ', $credit);
				}
			}

			// Determine if credits aren't listed in an array, but in a string. Make an array of it
			if (!is_array($credits)) {
				$credits = explode(', ', preg_replace("/\s+/", " ", $credits));
			}
						
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
						if ($tkey == 2 && ($tempstr[1] == 'van' || $tempstr[1] == 'ten' || $tempstr[1] == 'de' || $tempstr[1] == 'den' || strlen($tempstr[1]) < 10)) {
							$credits[$ckey] .= ' '.$strelem;

						}
						if ($tkey == 3 && ($tempstr[1] == 'van' && ($tempstr[2] == 'den') || $tempstr[2] == 'der' || $tempstr[2] == 'het' || $tempstr[2] == "'t")) {
							$credits[$ckey] .= ' '.$strelem;
						}
					}
					if (strlen($credit) == 0 || count($tempstr) <= 1) {
						unset($credits[$ckey]);
					}
				}
			}
			if (count($credits)>0) {
				$performers = $credits;
			}
		} else {

			$performers = getCredits($content);
		}
		
		if (count($performers) >= 1) {
			$performers = array_unique($performers, SORT_STRING);
			//Sort performers by length for keyword insertion
			#($performers,'sortByLength');
			$performerObj->success = true;
			$performerObj->performers = $performers;
			if (strlen($performers[0]) > 30) {
				$performerObj->success = false;
			}
		}
				
	}
	
	if ($performerObj->success == true) {
		return $performerObj->performers;
	} else {
		return false;
	}
}
# print_r(getPerformers("https://www.antwerpsymphonyorchestra.be/kidconcert-calamity-jane", array("performers"=>".cluster .main .artists .first .artist h1")));


#print_r(getPerformers("https://www.filmhuisalkmaar.nl/films/becoming-astrid", array("performers"=>".film-header .film-header-info .film-actors")));

#print_r(getPerformers("https://www.lantarenvenster.nl/programma/aaron-parks-little-big/", array("performers"=>".page-content .wp_theatre_prod .wp_theatre_prod_director + .page-content .user-content h5")));

#print_r(getPerformers("https://www.filmfestival.nl/films/tagged-2/", array("performers"=>".credits .wrap .part-two .inner .two")));
?>