<?php
// Crawl XML feed and parse productions
$source = $url;
$productions = array();

function validateDate($date, $format = 'd-m-Y')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

// Split tags to array element
$tags = explode('; ', $tags);
if (count($tags) > 1) {
	foreach ($tags as $key => $value) {
		$temp = explode(',', $value);
		$tags[$temp[0]] = str_replace('"', '', $temp[1]);
		unset($tags[$key]);
	}
	$tags['defined'] = true;
} else {
	// Predefined XML elements
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
		// Determine if the path has a defined key
		/*if (preg_match('/\[([^\]]*)\]/', $property, $key) == 1) {
    		$path = $path->$property[$key[1]];
		} else {*/
			$path = $path->$property;
		//}
		
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

	}
	
	// Filter by month
	if (date('Y-m', $time) == $month) {
			$productionObj = new stdClass();
			
			//Check if venue of production matches value stored in db
			if (strlen($tags['venue']) > 1 && (stripos($location['venue'][0], trimString(toPath($production, $tags['venue']))) === false)) {
				$venue = array();
				$venue[0] = trimString($production->location->venue);
			} else {
				$venue = $location['venue'];
			}
				

			// Put data to object
			$productionObj->title = trimString(toPath($production, $tags['title']));
			$productionObj->subtitle = trimString(toPath($production, $tags['subtitle']));
			$productionObj->venue = $venue;
			$productionObj->location = $location['city'];
			if (count(toPath($production, $tags['genre'])) > 1) {
				$productionObj->genre = listGenres(toPath($production, $tags['genre']));
			} else {
				$productionObj->genre = toPath($production, $tags['genre']);
			}
			//Check if genre exist
			if (count($productionObj->genre) < 1) {
				$productionObj->genre[0] = 'overig';
			}
			$productionObj->link = filter_var(trim(toPath($production, $tags['link'])), FILTER_SANITIZE_URL);
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
		
			//$productionObj->performers = getPerformers($productionObj->link, null);

			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			if (stripos($exclude, $productionObj->genre[0]) === false && stripos($exclude, $productionObj->genre[1]) === false && stripos($exclude, $productionObj->title) === false && stripos($productionObj->title, 'inleiding') === false && stripos($lastShow->status, "uitverkocht") === false && $lastShow->status !== "Geannuleerd") {
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