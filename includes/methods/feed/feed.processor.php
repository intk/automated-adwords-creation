<?php
// Crawl XML feed and parse productions
$source = $url;
$productions = array();

// XML scraper
$xml = simplexml_load_file($source, 'SimpleXMLElement', LIBXML_NOCDATA);
foreach ($xml->production as $production) {
	// Get last date of production
	$time = strtotime(filter_var($production->shows->show[count($production->shows->show)-1]->start, FILTER_SANITIZE_STRING));
	// Filter by month
	if (date('Y-m', $time) == $month) {
		$productionObj = new stdClass();
		// Put data to object
		$productionObj->title = filter_var(trim(str_replace('14+', '',$production->title)), FILTER_SANITIZE_STRING);
		$productionObj->subtitle = filter_var(trim($production->subtitle), FILTER_SANITIZE_STRING);
		$productionObj->genre = filter_var(trim($production->genres->genre[0]->attributes()->code), FILTER_SANITIZE_STRING);
		$productionObj->link = filter_var(trim($production->link), FILTER_SANITIZE_URL);
		$productionObj->date->time = $time;
		$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
		// Push object to productions array
		array_push($productions, $productionObj);
	}
}
?>