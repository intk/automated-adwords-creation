<?php
// Crawl XML feed and parse productions
$source = $url;
$productions = array();

// Sanitize & trim string
function trimString($string) {
	return str_replace(array("&#39;", "/"),array("'","-"), filter_var(trim($string), FILTER_SANITIZE_STRING));
}

//Sanitize genre array
function listGenres($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, filter_var(trim($genreItem->attributes()->code), FILTER_SANITIZE_STRING));
	}
	return $genreArr;
}

//Scrape performer names from web page
include('includes/methods/web/performers.processor.php');

// XML scraper
$xml = simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
foreach ($xml->production as $production) {
	// Get last date of production
	$lastShow = $production->shows->show[count($production->shows->show)-1];
	$time = strtotime(filter_var($lastShow->start, FILTER_SANITIZE_STRING));
	// Filter by month
	if (date('Y-m', $time) == $month) {
			$productionObj = new stdClass();
			
			//Check if venue of production matches value stored in db
			if (stripos($location['venue'][0], trimString($production->location->venue)) === false) {
				$location['venue'] = array();
				$location['venue'] = trimString($production->location->venue);
			} 
			
			// Put data to object
			$productionObj->title = trimString($production->title);
			$productionObj->subtitle = trimString($production->subtitle);
			$productionObj->venue = $location['venue'];
			$productionObj->location = $location['city'];
			$productionObj->genre = listGenres($production->genres->genre);
			//Check if genre exist
			if (count($production->genres->genre) < 1) {
				$productionObj->genre[0] = 'overig';
			}
			$productionObj->link = filter_var(trim($production->link), FILTER_SANITIZE_URL);
			$productionObj->date->time = $time;
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
			$productionObj->performers = getPerformers($productionObj->link, null);

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