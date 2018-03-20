<?php
// Crawl XML feed and parse productions
$source = $url;
$productions = array();

//Sanitize & trim string
function trimString($string) {
	return filter_var(trim($string), FILTER_SANITIZE_STRING);
}

// XML scraper
$xml = simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
foreach ($xml->production as $production) {
	// Get last date of production
	$time = strtotime(filter_var($production->shows->show[count($production->shows->show)-1]->start, FILTER_SANITIZE_STRING));
	// Filter by month
	if (date('Y-m', $time) == $month) {
		$productionObj = new stdClass();
		// Put data to object
		$productionObj->title = trimString($production->title);
		$productionObj->subtitle = trimString($production->subtitle);
		$productionObj->venue = trimString($production->location->venue);
		$productionObj->location = $location;
		$productionObj->genre = filter_var(trim($production->genres->genre[0]->attributes()->code), FILTER_SANITIZE_STRING);
		$productionObj->link = filter_var(trim($production->link), FILTER_SANITIZE_URL);
		$productionObj->date->time = $time;
		$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
		// Push object to productions array
		if (strpos($exclude, $productionObj->genre) === false && stripos($productionObj->title, 'inleiding') === false) {
			array_push($productions, $productionObj);
		}
	}
}
?>