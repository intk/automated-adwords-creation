<?php
// Crawl XML feed and parse productions
$source = $url;
$parVenue = $venue;
$productions = array();
header('Content-Type: application/json; charset=utf-8');

$lexiconTemp = new Lexicon();
$lexiconMonth = $lexiconTemp->monthFull;

#ini_set('display_errors', 1);


//Get date format from string
function dateFromString($string, $lexiconMonth) {

	//Convert string to a date string
	$time = '';
	$string = filter_var(trim(html_entity_decode(strip_tags(preg_replace("/\s+/", " ", $string)), ENT_QUOTES, "utf-8")), FILTER_SANITIZE_STRING);
	$string = str_replace('| ' , ' - ', $string);
	$splittedDate = preg_split('/(t\/m|&|tm| -| - | to| and | tot )+/i', $string);

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
	$date = trim(preg_replace("/(from|maandag| maa |mon|ma |dinsdag|din|tue|di|woensdag|woe|wed|wo|donderdag|don|thu|do|vrijdag|vri|fri|vr|zaterdag|zat|sat|za|zondag|zon|sun|zo|om)/i", "", $date));

	//$date = str_replace('.', '', $date);

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
	/*$date = str_ireplace(array("januari", "january", "februari", "february", "maart", "march", "mrt", "april", "mei", "may", "juni", "june", "juli", "july", "augustus", "august", "september", "oktober", "october", "okt", "november", "december", "v.a.", " -", "uur", ".", "th", ","), array("jan", "jan", "feb", "feb", "mar", "mar", "mar", "apr", "may", "may", "jun", "jun", "jul", "jul", "aug", "aug", "sep", "oct", "oct", "oct", "nov", "dec", "", "", "", ":", "", ""), $date);
	*/


	$date = str_ireplace(array_merge($lexiconMonth, array("v.a.", " -", "uur", ".", "th", ",")), 
		array("jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec", "", "", "", ":", "", ""), $date);

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


function validateDate($date, $format = 'd-m-Y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

// Split tags to array element
$tags = explode('; ', $tags);
foreach ($tags as $key => $value) {
		//Define first part as key and second part as value
		$temp = explode(',', $value);
		$tags[$temp[0]] = str_replace('"', '', $temp[1]);
		unset($tags[$key]);
}
if (count($tags) > 1) {
	$tags['defined'] = true;
} else {
	// Predefined XML elements
	$tags['venue'] = 'location/venue';
	$tags['venueHall'] = 'location/hall';
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
function listGenresP($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, strtolower(filter_var(trim($genreItem->attributes()->code), FILTER_SANITIZE_STRING)));
	}
	return $genreArr;
}

function listGenres($genre) {
	$genreArr = array();

	if (is_array($genre)) {

		// Determine if genre has to be filtered out from content
		foreach ($genre as $genreItem) {
			array_push($genreArr, trimString(strtolower(filter_var(trim($genreItem), FILTER_SANITIZE_STRING))));
		}

	} else {
		$genreArr[0] = $genre;
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
			$path = $path->{$property}[$matches[1][0]];
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

//Scrape performer names from web page
include('includes/methods/web/performers.processor.php');

// If pagination is given, iterate all pages
if (array_key_exists('pagination', $tags)) {
	$paginationString = $tags['pagination'];
	$number = $tags['pagecount'];
} else {
	$pagination = '';
	$number = 1;
}

for($i=1;$i<=$number;$i++) {

	if (array_key_exists('pagination', $tags)) {
		$pagination = $paginationString.$i;
	}


// JSON scraper
$json = preg_replace("/\s+/", " ", file_get_contents($source.$pagination));
$sourceArray = json_decode($json);


// If productions are subitems of parent element
if (strlen($tags['item']) > 1) {
	$sourceArray = toPath($sourceArray, $tags['item']);
}
foreach ($sourceArray as $production) {
	// Get last date of production
	if (!$tags['defined']) {
		$lastShow = $production->shows->show[count($production->shows->show)-1];
		$time = strtotime(filter_var($lastShow->start, FILTER_SANITIZE_STRING));
	} else {
		// Determine if the time has been given
		if (strpos($tags['date'], '.') <= -1) {
			if (array_key_exists('time', $tags) && strlen($tags['time']) > 1) {
				$time = strtotime(toPath($production, $tags['date']).' '.toPath($production, $tags['time']));
			} else {
				$time = strtotime(toPath($production, $tags['date']));
			}

			// Check if date is not given
			if (strlen($tags['date']) < 1) {
				$time = strtotime("+1 week");
			}
		}

	// Determine if date string is already a timestamp
	if (strpos($tags['date'], '.') <= -1 && (preg_match('~^[1-9][0-9]*$~', trimString(toPath($production, $tags['date']))) == 1)) {
		$time = trimString(toPath($production, $tags['date']));
	}

	//Determine if date can be found on webpage of performance
	if (strpos($tags['date'], '.') > -1) {
		$dom = new simple_html_dom(getWebPage(toPath($production, $tags['link'])));
		$time = dateFromString($dom->find($tags['date'], 0)->plaintext, $lexiconMonth);
	}


		// Determine if given date is invalid
		/*if (validateDate(toPath($production, $tags['date'])) == false || validateDate(date('d-m-Y', $time)) == false) {
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
			} else {
				$time = dateFromString(toPath($production, $tags['date']));
			}
		}*/

	}

	// Filter by month
	if (date('Y-m', $time) == $month || date('Y-m-d', $time) == $month || strtoupper($month) == "ALL" && $time > time()) {

			$productionObj = new stdClass();
			$otherHall = false;
		
			//Check if other venue hall matches value stored in db
			if (array_key_exists('hall', $tags) && strlen($tags['hall']) > 1) {
				$hallParts = explode(' + ', $tags['hall']);
				
				// Loop halls
				foreach ($hallParts as $hall) {
					if (stripos($hall, ' = ') !== false) {
						//Get hall and its venue
						$tempHall = explode(' = ', str_replace("'", "", $hall));

						if (stripos(trimString(toPath($production, $tags['venueHall'])), $tempHall[0]) !== false) {
							$hall = $tempHall[1];
							$venue = array();
							$venue[0] = $hall;
							$otherHall = true;	
						}
					} else {
						// Check if hall in feed matches hall in configuration
						if (stripos(trimString(toPath($production, $tags['venueHall'])), $hall) !== false) {
							$venue = array();
							$venue[0] = $hall;
							$otherHall = true;	
						}	
					}				

				}
				
			} else {
				$venue = $location['venue'];
			}
			//Check if venue of production matches value stored in db
			if (array_key_exists('venue', $tags) && (strlen($tags['venue']) > 1 && (stripos($location['venue'][0], trimString(toPath($production, $tags['venue']))) === false))) {
				$venue = array();
				$venue[0] = trimString(toPath($production, $tags['venue']));
				$otherHall = true;
			} else {
				$venue = $location['venue'];
			}

			if (strlen($venue[0]) < 1) {
				$venue = $location['venue'];
			}

			
			// Put data to object
			$productionObj->title = trimString(toPath($production, $tags['title']));
			if (strpos($productionObj->title, ' - ') !== false) {
				$productionObj->title = trimString(explode(' - ', $productionObj->title)[1]);
			}

			$productionObj->subtitle = trimString(toPath($production, $tags['subtitle']));
			if (strpos($productionObj->subtitle, ' - ') !== false) {
				$productionObj->subtitle = trimString(explode(' - ', $productionObj->subtitle)[1]);
			}
			
			$productionObj->venue = $venue;	
			$productionObj->location = $location['city'];

			if (count(toPath($production, $tags['genre'])) > 1) {
				$productionObj->genre = listGenres(toPath($production, $tags['genre']));
			} else {
				$productionObj->genre = listGenres(toPath($production, $tags['genre']));
			}
			//Check if genre exist
			if (count($productionObj->genre) < 1 || strlen($productionObj->genre[0]) < 1) {
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
					if (strpos($tags['genre'], ' ') === false && strpos($tags['genre'], '.') === false) {
						$parts[0] = $tags['genre'];
						$productionObj->genre = $parts;
					}
				}
			
			}

			if (strpos($tags['link'], 'http') !== false) {
				$productionObj->link = filter_var(trim($tags['link']), FILTER_SANITIZE_URL);
			} else {
				$productionObj->link = filter_var(trim(toPath($production, $tags['link'])), FILTER_SANITIZE_URL);
				if (strpos($productionObj->link, $tags['baseUrl']) === false) {
					$productionObj->link = $tags['baseUrl'].$productionObj->link;
				}
			}
			$productionObj->date = new stdClass();
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
			if (array_key_exists('performers', $tags)) {
				$performers = getPerformers($productionObj->link, array("performers"=>$tags['performers']));
				$productionObj->performers = $performers;

				//If one performer is listed, use it as the subtitle of the production object
				if (count($productionObj->performers) == 1 && strlen($productionObj->performers[0]) > 1) {
					$productionObj->subtitle = $productionObj->performers[0];
				}
			}
						
			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			/*if (stripos($lastShow->status, "uitverkocht") === false && $lastShow->status !== "Geannuleerd") {
				$excludeFound = false;

				foreach (explode(' ', $exclude) as $excl) {
					if (stripos($productionObj->title, $excl) > -1) {
						$excludeFound = true;
					}
					else if (stripos($excl, $productionObj->genre[0]) > -1) {
						$excludeFound = true;
					}
				}
				
				if (strlen($parVenue) > 1 && stripos($parVenue, $venue[0]) === false) {
					$excludeFound = true;
				}
				
				if ($excludeFound == false) {
					*/
					array_push($productions, $productionObj);
				//}
			//}

		}

}

} // Iteration of pages
?>