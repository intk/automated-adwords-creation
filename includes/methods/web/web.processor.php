<?php
// Crawl XML feed and parse productions
#ini_set('display_errors', 1);

$source = $url;
$tempDate = '';
$productions = array();

$lexiconTemp = new Lexicon();

// Sanitize & trim string
include('includes/methods/filter/filter.inc.php');

function validateDate($date, $format = 'd-m-Y') {

    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;

}


//Get date format from string
function dateFromString($string, $lexicon) {

	//Convert string to a date string
	$string = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $string)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	$string = str_replace(array('&nbsp;','&#39;','| ' ), array('','\'', ', '), $string);
	$splittedDate = preg_split('/(t\/m|&|tm| -| - | to| al | and | tot )+/i', $string);

	// Determine if date and time are separated, choose part with date format
	if (strpos($splittedDate[count($splittedDate)-1], 'uur') > 1 || strpos($string, ':') > 1) {
		$date = $splittedDate[0];
	} else {
		$date = $splittedDate[count($splittedDate)-1];
	}
	if (strpos($date,'+') !== false) {
		$date = substr($date, 0, strpos($date,'+'));
	}

	// Exclude days of the week and their abbreviations
	$date = trim(preg_replace("/(from|maandag|monday|lun. |mar. |mer. |jeu. |ven. |sam. |dim. | maa |mon|ma |dinsdag|tuesday|din|tue|di|woensdag|wednesday|woe|wed|wo|donderdag|thursday|don|thu|do|vrijdag|friday|vri|fri|vr|zaterdag|saturday|zat|sat|za|zondag|sunday|zon|sun|zo|om)/i", "", $date));
	$date = trim(str_replace(array('d\'', ' de '), array('', ' '), $date));

	//Determine if wrong date format has been used
	if (substr_count($date, '.') > 1) {
		// Execute preg_match
		$date = explode('-',$date)[count(explode('-',$date))-1];
		if (preg_match("/\d{2}.\d{2}.\d{2}/", $date, $match) && !preg_match("/\d{2}.\d{2}.\d{4}/", $date, $match)) {
			$dateTemp = trim(str_replace('.','-', $date));
			$d = DateTime::createFromFormat('d-m-y', $dateTemp);
			$time = strtotime($d->format('Y-m-d'));
		}
		else if (preg_match("/\d{2}.\d{2}.\d{4}/", $date, $match)) {
			$dateTemp = trim(str_replace('.','-', $match[0]));
			$d = DateTime::createFromFormat('d-m-Y', $dateTemp);
			$time = strtotime($d->format('Y-m-d'));
		}
	}

	// If date format is DD.MM or DD-MM
	if (substr_count($date, '.') == 1 || preg_match("/\d{2}\-\d{2}/", $date, $match)) {

		if (preg_match("/\d{2}.\d{2}|\d{2}\-\d{2}/", $date, $match)) {
			$dateTemp = trim(str_replace('.','-', $date));
			// Determine year if only day and month is given 
			$dateEl = explode('-', $dateTemp);
			if ($dateEl[1] >= date('m')) {
				$tempYear = date('y');
			} else {
				$tempYear = date('y')+1;
			}

			$d = DateTime::createFromFormat('d-m-y', $dateTemp.'-'.$tempYear);
			$time = strtotime($d->format('Y-m-d'));
		}
	}

	# Determine if dd/mm/YYYY H:i date format is used
	if (substr_count($date, ':') == 1) {
		if (preg_match("/\d{2}\/\d{2}\/\d{4}/", $date, $match)) {
			$match[0] = str_replace('/', '-', $match[0]);
			$d = DateTime::createFromFormat('d-m-Y', $match[0]);
			$time = strtotime($d->format('Y-m-d'));
		}
	}


	/*
	if (substr_count($date, '-') == 1) {

		if (preg_match("/\d{2}-\d{2}/", $date, $match)) {
			$dateTemp = trim($date);
			// Determine year if only day and month is given 
			$dateEl = explode('-', $dateTemp);
			if ($dateEl[1] >= date('m')) {
				$tempYear = date('y');
			} else {
				$tempYear = date('y')+1;
			}

			$d = DateTime::createFromFormat('d-m-y', $dateTemp.'-'.$tempYear);
			$time = strtotime($d->format('Y-m-d'));
		}
	}
	*/
	if (substr_count($date, '-') > 1) {
		$date =  preg_replace('/[\[{\(].*[\]}\)]/u', '', $date);
		// Execute preg_match
		if (preg_match("/\d{2}-\d{2}-\d{4}/", $date, $match)) {
			$d = DateTime::createFromFormat('d-m-Y', $match[0]);
			$time = strtotime($d->format('Y-m-d'));
		}
	}

	if (substr_count($date, ':') == 1 && substr_count($date, '-') > 1) {
		if (preg_match("/\d{2}\-\d{2}\-\d{4}/", $date, $match)) {
			$match[0] = str_replace('/', '-', $match[0]);
			$d = DateTime::createFromFormat('d-m-Y', $match[0]);
			$time = strtotime($d->format('Y-m-d'));
		}
	}

	
	// Replace months and their abbreviations
	$date = str_ireplace(array_merge($lexicon->monthFull, array("v.a.", " -", "uur", ".", "th", ",")), 
		array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec", "", "", "", ":", "", ""), $date);

	$date = str_ireplace($lexicon->monthAbbr, 
		array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"), $date);

	$date = str_ireplace(array_map(function($str) {return str_replace('.', '', $str);}, $lexicon->monthAbbr), 
		array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"), $date);

	//Convert string to date format
	$dateArray = explode(' ', trim($date));
	$dateArray[1] = str_ireplace(array('01','02','03','04','05','06','07','08','09','10','11 ','12'), array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov ','dec'), $dateArray[1]);

	//if (intval($dateArray[0]) < 10 && strpos($dateArray[0], '0') !== false) { $dateArray[0] = substr($dateArray[0], 1); }
    if ($time < time()) {

		//Determine if date string is already a valid date format
		if (validateDate($dateArray[0]) || validateDate(date('d-m-Y', strtotime($date))) || validateDate($date)) {
			if (strpos($date, ':') !== false) {
				//$time = strtotime($dateArray[0].' '.$dateArray[count($dateArray)-1]);
				//Define the year
				if (strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.date('Y')) < time()) {
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
			} else {
				$time = strtotime($dateArray[0]);
				if (strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.date('Y')) < time()) {
					$year = date('Y')+1;
				} else {
					$year = date('Y');
				}
				$time = strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.$year);
			}

		} else {

			//Define the year
			if (strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.date('Y')) < time()) {
				$year = date('Y')+1;
			} else {
				$year = date('Y');
			}
			if ($dateArray[2] == date('Y') || $dateArray[2] == date('Y')+1) {
				$year = $dateArray[2];
			}
			//$year = '00';
			//Convert date to timestamp
			if (strpos($date, ':') !== false) {
				$time = strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.$year.' '.$dateArray[2]);
			} else {
				$time = strtotime($dateArray[0].' '.ucfirst($dateArray[1]).' '.$year);
			}
			
		}
	}
	return $time;

}

//Sanitize genre array
function listGenres($genre) {
	$genreArr = array();
	if (strpos($genre, ',') > 1) {
		$genre = explode(',', $genre);
	}
	if (count($genre) > 1) {
		foreach ($genre as $genreItem) {
			array_push($genreArr, trim(filter_var(trim(strtolower($genreItem)), FILTER_SANITIZE_STRING)));
		}
	}
	return $genreArr;
}


// Include simple_html_dom module to scrape a web page
// Parse spreadsheet data and filter on the performance name to derive similar artists
include('includes/methods/web/performers.processor.php');


// Split tags to array element
$tags = explode('; ', $tags);
foreach ($tags as $key => $value) {
	$temp = explode(',', $value);
	// Check if multiple selectors are given in tag
	if (count(explode(' + ', $temp[1])) > 1) {
		if (strpos($temp[1], '[') == false) {
			$temp[1] = str_replace(" + ", ", ", $temp[1]);
		}
	}

	$tags[$temp[0]] = str_replace('"', '', $temp[1]);


	unset($tags[$key]);

}


// If pagination is given, iterate all pages
if (array_key_exists('pagination', $tags)) {
	$paginationString = $tags['pagination'];
	$number = $tags['pagecount'];
} else {
	$pagination = '';
	$number = 1;
}


//for ($pag = 0; $pag < $pagCount; $pag++) {
	//print_r(array($source.'&p='.$pag));
	/*if (strlen($tags['pagination']) > 1) {
		$dom = new simple_html_dom(getWebPage($source.'&p='.$pag));
	} else {*/

for($i=1;$i<=$number;$i++) {

	if (array_key_exists('pagination', $tags)) {
		$pagination = $paginationString.$i;
	}


		$dom = new simple_html_dom(getWebPage($source.$pagination)); 
	//}
foreach ($dom->find($tags['container'].' '.$tags['item']) as $keyA => $production) {

	//Check if title, subtitle and date are placed in same tag
	if ($tags['title'] == $tags['subtitle'] && $tags['date'] == $tags['title']) {
		$elementsArray = explode('<br>', preg_replace("/\s+/", " ", $production->find($tags['title'], 0)));
		foreach($elementsArray as $key => $val) {
			$elementsArray[$key] = $val;
		}
		$title = $elementsArray[0];
		$subtitle = $elementsArray[1];
		$date = trimString($elementsArray[2]);
		

		// Determine if a genre has been given for the performance
		if (strlen($production->find($tags['genre'], 0)->plaintext) > 2) {
			$genre = array(strtolower($production->find($tags['genre'], 0)->plaintext)); 
		} else {
			$genre = "voorstelling";
		}

	}


	if ($tags['title'] == $tags['subtitle'] && $tags['date'] !== $tags['title']) {
		if (strpos($production->find($tags['title'], 0), '<br>') > 1) {
			$elementsArray = explode('<br>', $production->find($tags['title'], 0));
			$title = filter_var(explode('<br>', $production->find($tags['title'], 0))[count($elementsArray)-1], FILTER_SANITIZE_STRING);
			$subtitle = '';
			$date = str_replace("'", '20', preg_replace("/\s+/", " ", $production->find('a .date', 0)->plaintext));
			//print_r(array($date, dateFromString($date)));


		} else {
			$title = trimString($production->find($tags['title'].' h5', 0)->plaintext);
			$subtitle = trimString(substr($production->find($tags['subtitle'], 0), strpos($production->find($tags['subtitle'], 0), '</h5>')));
			$date = explode(' ', trimString($production->find($tags['date'], 0)->plaintext));
			$date = $date[1].' '.$date[0].' '.$date[2];
		}
				
		
		// Determine if a genre has been given for the performance
		if (strlen($production->find($tags['genre'], 0)->plaintext) > 2) {
			$genre = array(strtolower($production->find($tags['genre'], 0)->plaintext)); 
		} else {
			$genre = "voorstelling";
		}



	
	} else {
		$title = $production->find($tags['title'], 0)->plaintext;
		$subtitle = $production->find($tags['subtitle'], 0)->plaintext;
		if (strpos($subtitle, "&nbsp;") !== false || strlen($subtitle) < 2) {
			$subtitle = trimString($production->find($tags['subtitle'], 1)->plaintext);
		}
		if (preg_match("/\d{4}-\d{2}-\d{2}/", $tags['date'], $match)) {
			$date = trimString($tags['date']);
			$genre = $production->find($tags['genre'], 0)->plaintext;
		} else {
			$genre = $production->find($tags['genre'], 0)->plaintext;
			if (count($production->find($tags['date'])) > 3) {
				$date = trimString($production->find($tags['date'], -1)->plaintext);
			} else {

				// Use last date if multiple dates are given
				$dateRaw = $production->find($tags['date'], 0);
				// Find blank rows as seperator of the dates and its offset
				if (preg_match_all('/(<br\/>|<br>)/', $dateRaw, $matches, PREG_OFFSET_CAPTURE) && preg_match("/\<br\>\d{2}<br\>\d{2}/", $dateRaw, $match) == false) {
					// Return the portion of dates string specified by the seperator offset as start parameter
					$date = trimString(substr($dateRaw, $matches[0][count($matches[0])-1][1]));
					if (strlen($date) <= 1) {
						$date = trimString(substr($dateRaw, $matches[0][count($matches[0])-2][1]));
					}
				} else {

					$date = trimString($production->find($tags['date'], -1)->plaintext);
				}
			}
			
		}

		// Determine if date is an attribute of a dom element
		if (array_key_exists('date', $tags) && strpos($tags['date'], 'itemprop') > 1) {
			$date = trimString($production->find($tags['date'], -1)->content);
		}	
	}

	// Check if date is not given
	if (strlen($tags['date']) < 1) {
		$date = date('d-m-Y', strtotime("+1 week"));
	}

	// Check if date format is given
	if (validateDate($tags['date'])) {
		$date = $tags['date'];
	}

	// Check if title is not given
	if (strlen($tags['title']) < 1) {
		$titleparts = explode('/', $production->href);
		$title = str_replace('-', ' ', $titleparts[count($titleparts)-1]);
		// If title is placed directly within event item, use it. Remove additional divs
		if (strlen($title) < 1) {
			$title = str_replace($production->find('div',0)->plaintext, '', trimString($production->plaintext));
		}
	}

	if (strlen($title) < 1 && strlen($subtitle) > 1) {
		$title = $subtitle;
		$subtitle = '';
	}
	if (strlen($title) < 1 && strlen($subtitle) < 1) {
		$title = $genre;
		$subtitle = '';
	}


	// Determine if a genre has been given for the performance

	//$genre = array(strtolower($dom->find($tags['container'], 0)->find(".agenda_tabs", $keyA)->find(".col-md-3 .tab_cat p", 0)->plaintext));
	
	// Determine if given date is invalid
		if (validateDate($date) == false  || validateDate(date('d-m-Y',$time)) == false) {
			$link = $production->find($tags['link'], 0)->href;

			// Check for a valid date format in the given url
			if (preg_match("/\d{2}-\d{2}-\d{4}/", $link, $match)) {
				$time = strtotime($match[0]);

				// Check for valid time format in the given url
				if (preg_match("/\d{2}-\d{2}-\d{4}-\d{2}-\d{2}/", $link, $match)) {
					
					// Replace last '-' with ':' for valid time
					$search = '-';
					$subject = $match[0];
					$pos = strpos_all($subject, $search);
					
					// Add time to date string and create a timestamp of it
					if($pos !== false) {
						$subject = substr_replace($subject, ':', $pos[count($pos)-1], strlen($search));
						$subject = substr_replace($subject, ' ', $pos[count($pos)-2], strlen($search));
						$time = strtotime($subject);
					}

				}

				// Check for a valid date format in the given url
				if (preg_match("/\d{4}-\d{2}-\d{2}/", $link, $match)) {
					$time = strtotime($match[0]);
				} else {
					$time = time();
				}
			}
			
		}	

	//Determine if date can be found on webpage of performance
	if (strlen($date) == 0 && strpos($tags['date'], '.') > -1) {
		// Determine if URL starts with //. Remove it from the URL
		$linkElement = $link;
		if (strpos($linkElement, '//') > -1 && strpos($linkElement, 'http') < -1) {
			$linkElement = str_replace('//'.parse_url($linkElement)['host'], '', $linkElement);
		}
		$link = $linkElement;
		$dom = new simple_html_dom(getWebPage($tags['baseUrl'].$link));
		$date = $dom->find($tags['date'], -1)->plaintext;
	}

	// If date is specified in datetime attribute of time element
	if ($production->find($tags['date'], 0)->datetime) {
		$date = filter_var($production->find($tags['date'], 0)->datetime, FILTER_SANITIZE_STRING);
	}

	// Get last date of production
	$time = dateFromString($date, $lexiconTemp);


	//Custom added 
	#print_r(array($title, $tags['genre'], $production->find($tags['genre'], 0)->plaintext, $tags['subtitle'], $subtitle, $tags['date'], $date, $tempDate, $time, date('Y-m-d', $time), $tags['link'], $link));

	// Filter by month
	if ((date('Y-m', $time) == $month || strtoupper($month) == "ALL") && $time > time()) {

		
		//Check if genre exist
			$productionObj = new stdClass();			
			// Put data to object
			$productionObj->title = trimString($title);
			$productionObj->subtitle = trimString($subtitle);

			// Determine if title element contains subtitle
			if (strlen($productionObj->subtitle) < 1 && strpos($productionObj->title, ' - ') > 1) {
				$titleParts = explode(' - ', $productionObj->title);
				$productionObj->title = $titleParts[0];
				$productionObj->subtitle = $titleParts[1];
			}
			$productionObj->venue = $location['venue'];
			$productionObj->location = $location['city'];			
			
			$productionObj->genre = listGenres($genre);
			if (count($productionObj->genre) < 1 || strpos($productionObj->genre[0], ' ') !== false) {
				//$productionObj->genre[0] = 'voorstelling';
				$productionObj->genre[0] = strtolower(trimString($genre));

				if (strpos($tags['genre'], '.') === false) {
					$productionObj->genre[0] = $tags['genre'];
				}
			}

			if (strpos($tags['genre'], '/') !== false) {
				$parts = explode(' ', $tags['genre']);
				$needleHaystack = explode('/', $parts[0]);
				if (stripos($production->find($needleHaystack[0]), $needleHaystack[0]) !== false) {
					$productionObj->genre[0] = $needleHaystack[0];
				} else {
					$productionObj->genre[0] = $parts[1];
				}
			} else {
				//$productionObj->genre[0] = 'overig';
				if (strpos($tags['genre'], ' ') !== false && strpos($tags['genre'], '.') === false) {
					$parts = explode(' ', $tags['genre']);
					$productionObj->genre = $parts;
				}
			}

			if (!$tags['link'])	{
				$link = 'a';
			}
			if (strlen($tags['link']) < 1)	{
				$link = $production->href;
			}
			 else {
				$link = $tags['link'];
			}

			if (strpos($production->find($link, 1)->href, $tags['baseUrl']) !== false) {
				$productionObj->link = filter_var(trim($production->find($link, 0)->href), FILTER_SANITIZE_URL);
			} else {
				// Determine if item is an anchor link
				if (strlen($production->href) > 1) {
					$productionObj->link = filter_var(trim($production->href), FILTER_SANITIZE_URL);

					//if (strpos($production->href, $tags['baseUrl']) !== false) {
					//	$productionObj->link = filter_var(trim($production->href), FILTER_SANITIZE_URL);
					//} else {
					//	$productionObj->link = filter_var(trim($tags['baseUrl'].$production->href), FILTER_SANITIZE_URL);
					//}
				} else {

					$linkElement = filter_var(trim($production->find($link, 0)->href), FILTER_SANITIZE_URL);

					// Determine if URL starts with //. Remove it from the URL
					if (strpos($linkElement, '//') > -1 && strpos($linkElement, 'http') < -1) {
						$linkElement = str_replace('//'.parse_url($linkElement)['host'], '', $linkElement);
					}
					$productionObj->link = filter_var(trim($tags['baseUrl'].$linkElement), FILTER_SANITIZE_URL);
				}
			}


			// Remove domainname from url when its added twice
			if (substr_count($productionObj->link, $tags['baseUrl']) > 1) {
				$productionObj->link = substr($productionObj->link, strpos($productionObj->link, $tags['baseUrl'])+strlen($tags['baseUrl']));
			}

			//echo $productionObj->link;


			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);

			if (array_key_exists('performers', $tags)) {
				if (strlen($productionObj->link) > 1) {
					$productionObj->performers = getPerformers($productionObj->link, array("performers"=>$tags['performers']));
				}
				//$productionObj->performers = getsimilarPerformers($productionObj->title);
				if (count($productionObj->performers) == 1 && strlen($productionObj->performers[0]) > 1) {
					$productionObj->subtitle = $productionObj->performers[0];
				}
			}

			// If subtitle does not exist in agenda item, try to find performer on the the performance page itself
			if (strlen($productionObj->subtitle) <= 1 && strlen($tags['subtitle']) > 1) {

				# Suppress warning when function can't be executed
				$productionObj->subtitle = getPerformers($productionObj->link, array("performers"=>$tags['subtitle']));
				

			}


			//$productionObj->subtitle = implode(', ', getPerformers($productionObj->link, array("performers"=>$tags['subtitle'])));
			if (array_key_exists('location', $tags)) {
				$venueArr = explode('|', $production->find($tags['location'], 0)->plaintext);
				$locationArr = explode('-',  $venueArr[0]);
				$productionObj->location = trim($locationArr[0]);
				//$productionObj->venue[0] = trim($locationArr[0]).' '.$productionObj->location;

			}

			#print_r($productionObj);



			//$productionObj->link = str_replace("https://stadstheater.nl//stadstheater.nl", "https://stadstheater.nl", $productionObj->link);

			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			
				$excludeFound = false;
				foreach (explode(' ', $exclude) as $excl) {
					if (stripos($productionObj->title, $excl) > -1) {
						$excludeFound = true;
					}
					else if (stripos($excl, $productionObj->genre[0]) > -1) {
						$excludeFound = true;
					}
				}

				
				if ($excludeFound == false) {
					array_push($productions, $productionObj);
				}

		}

}

} //Used for pagination

?>