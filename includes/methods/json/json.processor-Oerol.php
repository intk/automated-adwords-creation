<?php
// Crawl XML feed and parse productions
$source = json_decode(file_get_contents($url));
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
function listGenres($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, strtolower(filter_var(trim($genreItem->attributes()->code), FILTER_SANITIZE_STRING)));
	}
	return $genreArr;
}

// Create path of '/' seperated string
function toPath($xml, $string) {
	$parts = explode('/', $string);
	$path = $xml;
	if (strlen($string) > 1) {
		foreach ($parts as $property) {
			// Determine if the path has a defined key
			/*if (preg_match('/\[([^\]]*)\]/', $property, $key) == 1) {
				$path = $path->$property[$key[1]];
			} else {*/
				$path = $path->$property;
			//}

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

// json scraper
$json = $source;

$cat = array("theater", "expeditie", "muziek", "straat", "verdieping");

foreach (toPath($json, $tags['item']) as $production) {
	
	// Get last date of production
		if (count($production->real_days) > 0) {
			$lastShow = $production->real_days[count($production->real_days)-1][0];
			$lastDate = '2018-'.substr($lastShow, 2).'-'.substr($lastShow, 0, -2);
		} else {
			$lastShow = $production->playtime;
			if (preg_match_all("/\d{2}, |\d{2} /", $lastShow, $match)) { 
				foreach ($match[0] as $key => $val) {
					$match[0][$key] = trim(str_replace(',', '', $val));
					if (strpos($val, '00') !== false || intval($val) > 25) {
						unset($match[0][$key]);
					}
				}
				if (count($match[0]) > 0) {
					$lastDate = '2018-06-'.$match[0][count($match[0])-1];
				} else {
					$lastDate = null;
				}
			}
		}
	
		$time = strtotime(filter_var($lastDate, FILTER_SANITIZE_STRING));
	
	// Filter by month
	if (date('Y-m', $time) == $month || strtoupper($month) == "ALL") {
			$productionObj = new stdClass();
		

			// Put data to object
			$productionObj->title = html_entity_decode(trimString(toPath($production, $tags['title'])), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$productionObj->subtitle = html_entity_decode(trimString(toPath($production, $tags['subtitle'])), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$productionObj->venue = $location['venue'];			
			$productionObj->location = $location['city'];
			$productionObj->genre[0] = $cat[toPath($production, $tags['genre'])-1];
			if (count($productionObj->genre) < 1 || strlen($productionObj->genre[0]) <= 2) {
				$productionObj->genre[0] = 'overig';
			}
			
			$productionObj->link = "https://oerol.nl/festival/programma-a-z/?categorie=".$productionObj->genre[0];
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
			if (array_key_exists('performers', $tags)) {
				$productionObj->performers = getPerformers($productionObj->link, $tags['performers']);
			}
						
			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			if (stripos($lastShow->status, "uitverkocht") === false && $lastShow->status !== "Geannuleerd") {
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

}
?>