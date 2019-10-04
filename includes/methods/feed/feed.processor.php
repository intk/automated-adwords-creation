<?php
// Crawl XML feed and parse productions
$source = $url;
$productions = array();

$lexiconTemp = new Lexicon();

function validateDate($date, $format = 'd-m-Y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function splitTags($tags) {
	$tags = explode('; ', $tags);
	foreach ($tags as $key => $value) {
		$temp = explode(',', $value);
		$tags[$temp[0]] = str_replace('"', '', $temp[1]);
		unset($tags[$key]);
	}
	return $tags;
}

// Split tags to array element
$tags = splitTags($tags);
if (count($tags) > 1) {
	$tags['defined'] = true;
} else {
	// Predefined XML elements
	$tags['venue'] = 'location/venue';
	$tags['item'] = 'production';
	$tags['title'] = 'title';
	$tags['subtitle'] = 'subtitle';
	$tags['genre'] = 'genres/genre';
	$tags['link'] = 'link';
	$tags['defined'] = false;
}

// Sanitize & trim string
include('includes/methods/filter/filter.inc.php');


//Sanitize genre array
function listGenres($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, filter_var(trim($genreItem->attributes()->code), FILTER_SANITIZE_STRING));
	}
	return $genreArr;
}

// Create path of '/' seperated string
function toPath($xml, $string) {
	$parts = explode('/', $string);
	$path = $xml;
	foreach ($parts as $property) {
		// Determine if the path string has a defined key and use it
		if (preg_match_all("/\[([^\]]*)\]/", $property, $matches)) {
			//Remove brackets with key from string
			$property = str_replace($matches[0][0], '', $property);

			// Select property by name and key
			$path = $path->{$property}[intval($matches[1][0])];
		} else {
			$path = $path->$property;
		}
	}
	return $path;
}

function strpos_all($haystack, $needle) {
    $offset = 0;
    $allpos = array();
    while (($pos = strpos($haystack, $needle, $offset)) !== false) {
        $offset   = $pos + 1;
        $allpos[] = $pos;
    }
    return $allpos;
}

//Get date format from string
function dateFromString($string, $lexicon) {


	//Convert string to a date string
	$string = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $string)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	$string = str_replace(array('&#39;','| ' ), array('\'', ' - '), $string);
	$splittedDate = preg_split('/(t\/m|&|tm| -| - | to| al | and | tot )+/i', $string);

	// Determine if date and time are separated, choose part with date format
	if (strpos($splittedDate[count($splittedDate)-1], 'uur') > 1 || strpos($string, ':') > 1) {
		$splittedDate[0] = str_replace('.', ':', $splittedDate[0]);
		$date = $splittedDate[0];
	} else {
		$date = $splittedDate[count($splittedDate)-1];
	}
	if (strpos($date,'+') !== false) {
		$date = substr($date, 0, strpos($date,'+'));
	}

	// Exclude days of the week and their abbreviations
	$date = trim(preg_replace("/(from|maandag|monday| maa |mon|ma |dinsdag|tuesday|din|tue|di|woensdag|wednesday|woe|wed|wo|donderdag|thursday|don|thu|do|vrijdag|friday|vri|fri|vr|zaterdag|saturday|zat|sat|za|zondag|sunday|zon|sun|zo|om)/i", "", $date));
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
			$dateTemp = trim(str_replace('.','-', $date));
			$d = DateTime::createFromFormat('d-m-Y', $dateTemp);
			$time = strtotime($d->format('Y-m-d'));
		}
	}

	if (substr_count($date, '.') == 1) {

		if (preg_match("/\d{2}.\d{2}/", $date, $match)) {
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
	else if (substr_count($date, '-') > 1) {
		$date =  preg_replace('/[\[{\(].*[\]}\)]/u', '', $date);
		// Execute preg_match
		if (preg_match("/\d{2}-\d{2}-\d{4}/", $date, $match)) {
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

//Scrape performer names from web page
include('includes/methods/web/performers.processor.php');

// XML scraper
$xml = simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
foreach (toPath($xml, $tags['item']) as $production) {

	// Get last date of production
	if (!$tags['defined']) {
		$lastShow = $production->shows->show[count($production->shows->show)-1];
		$time = strtotime(filter_var($lastShow->start, FILTER_SANITIZE_STRING));
	} else {
		
		// Determine if the time has been given
		if (strlen($tags['time']) > 1) {
			$time = strtotime(toPath($production, $tags['date']).' '.toPath($production, $tags['time']));
		} else {
			$time = strtotime(toPath($production, $tags['date']));
		}
		
		// Determine if given date is valid
		if (validateDate(toPath($production, $tags['date'])) == false) {
			// Check for a valid date format in the given url
			if (preg_match("/\d{2}-\d{2}-\d{4}/", toPath($production, $tags['link']), $match)) {
				$time = strtotime($match[0]);
				
				// Check for valid time format in the given url
				if (preg_match("/\d{2}-\d{2}-\d{4}-\d{2}-\d{2}/", toPath($production, $tags['link']), $match)) {
					
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
			}
		}

		//Determine if date can be found on webpage of performance
		if (strpos($tags['date'], '.') > -1) {
			$dom = new simple_html_dom(getWebPage(toPath($production, $tags['link'])));
			$time = dateFromString(trimString($dom->find($tags['date'], 0)->plaintext), $lexiconTemp);

		}

	}

	// Filter by month
	if (date('Y-m', $time) == $month || strtoupper($month) == "ALL") {
		
			$productionObj = new stdClass();
			
			//Check if venue of production matches value stored in db
			if ((strlen($tags['venue']) > 1 && (stripos($location['venue'][0], trimString(toPath($production, $tags['venue']))) === false))) {
				$venue = array();
				$venue[0] = trimString(toPath($production, $tags['venue']));
				$otherHall = true;
			} else {
				$venue = $location['venue'];

			}

			if (strlen($venue[0]) < 1) {
				$venue = $location['venue'];

				// Replace venue with hall if hall values exist in db
				if (array_key_exists('hall', $tags) && !$tags['defined']) {
					foreach(explode("+", $tags['hall']) as $hall) {
						if (stripos($production->location->hall, trim($hall)) !== false) {
							$venue[0] = trim($hall);
						}
					}
				}
			}
				

			// Put data to object
			$productionObj->title = trimString(toPath($production, $tags['title']));
			$productionObj->subtitle = trimString(toPath($production, $tags['subtitle']));

			// Determine if title element contains subtitle
			if (strlen($productionObj->subtitle) < 1 && strpos($productionObj->title, ' - ') > 1) {
				$titleParts = explode(' - ', $productionObj->title);
				$productionObj->title = $titleParts[0];
				$productionObj->subtitle = $titleParts[1];
			}


			$productionObj->venue = $venue;
			$productionObj->location = $location['city'];
			if (count(toPath($production, $tags['genre'])) > 1) {
				$productionObj->genre = listGenres(toPath($production, $tags['genre']));
			} else {
				$productionObj->genre = listGenres(toPath($production, $tags['genre']));
			}
			//Check if genre exist
			if (count($productionObj->genre) < 1) {
				// Use genres listed in configuration
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
			
			}

			// Check if predefined genre exists in url
			if ($tags['genre'] == $tags['link']) {
				$foundGenre = false;
				foreach ($lexiconTemp->adPlacement as $key => $tempGenre) {

					if (stripos(trim(toPath($production, $tags['genre'])), $tempGenre[0]) > -1) {
						$productionObj->genre[0] = $tempGenre[0];
						$foundGenre = true;
					} 
					else {
						if (!$foundGenre) {
							$productionObj->genre[0] = $lexiconTemp->adPlacement['performance'][0];
						}
					}

				}
		
			}


			#$productionObj->genre[0] = 'concert';
			#$productionObj->genre[1] = 'muziek';
			$productionObj->link = filter_var(trim(toPath($production, $tags['link'])), FILTER_SANITIZE_URL);
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
		
			//$productionObj->performers = getPerformers($productionObj->link, null);

			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			if (stripos($exclude, $productionObj->genre[0]) === false && stripos($exclude, $productionObj->genre[1]) === false && stripos($exclude, $productionObj->title) === false && stripos($productionObj->title, 'inleiding') === false && stripos($lastShow->status, "uitverkocht") === false && stripos($lastShow->status, "geannuleerd") === false) {
				$excludeFound = false;
				foreach (explode(' ', $exclude) as $excl) {
					if (stripos($productionObj->title, $excl) > -1) {
						$excludeFound = true;
					}
				}
				
				if ($excludeFound == false) {
					array_push($productions, $productionObj);
				}
			}
		}

}
?>