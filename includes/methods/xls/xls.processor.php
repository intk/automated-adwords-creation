<?php
$productionData = array();
$productions = array();
$columns = array();
include('includes/methods/xls/simplexlsx.php');

// Split tags to array element
$tags = explode('; ', $tags);
foreach ($tags as $key => $value) {
	$temp = explode(',', $value);
	$tags[$temp[0]] = str_replace('"', '', $temp[1]);
	unset($tags[$key]);
}

//Validate date string
function validateDate($date, $format) {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

//Sanitize genre array
function listGenres($genre) {
	$genreArr = array();
	foreach ($genre as $genreItem) {
		array_push($genreArr, trim(filter_var(trim(strtolower($genreItem)), FILTER_SANITIZE_STRING)));
	}
	return $genreArr;
}


if ($xlsx = SimpleXLSX::parse($url)) {
	// Get column names and store them in array
	foreach ($xlsx->rows()[0] as $column) {
		if (strlen($column)>1) {
			array_push($columns, str_replace(' ', '_', strtolower($column)));
		}
	}
	
	// Get production data and remove empty rows
	foreach ($xlsx->rows() as $key => $row) {
		if ($key > 0) {
			foreach($columns as $ckey => $column) {
				$productionData[$key][$column] = $row[$ckey];
			}
		}
	}
	
	//Scrape performer names from web page
	include('includes/methods/web/performers.processor.php');
	
	// Create productions object from production data
	foreach ($productionData as $production) {
		// Define time and link variable
		$time = '';
		$link = '';

		// Iterate production object
		foreach ($production as $key => $data) {

			// Sanitize string
			$production[$key] = filter_var(trim($data), FILTER_SANITIZE_STRING);
			$data = filter_var(trim($data), FILTER_SANITIZE_STRING);

			//Split link from production data
			if (filter_var($data, FILTER_VALIDATE_URL) == true) {
				$link = $data;
			}

			// Split date from production data
			if ((bool)strtotime($data)) {
				if (strlen($time) > 1) {
					$time .= ' '.$data;
				} else {
					$time = $data;
				}

			} else {

			}


		}

		// Filter by month
		if (date('Y-m', strtotime($time)) == $month || strtoupper($month) == "ALL") {
			$productionObj = new stdClass();


			$productionObj->title = $production[$tags['title']];
			$productionObj->genre[0] = $production[$tags['category']];
			$productionObj->subtitle = $production[$tags['artist']];
			$productionObj->venue = $location['venue'];
			$productionObj->location = $location['city'];

			if (count($productionObj->genre) < 1 || $productionObj->genre[0] !== '') {
				//$productionObj->genre[0] = 'voorstelling';
				//$productionObj->genre[0] = strtolower(trimString($genre));

				if (strpos($tags['genre'], '.') === false) {
					$productionObj->genre[0] = $tags['genre'];
				}
			}

			//Add genre
			/*$productionObj->genre = listGenres($tags['genre']);
			if (count($productionObj->genre) <= 1) {
					if (strlen($tags['category']) > 1) {
						$productionObj->genre[0] = $tags['category'];
					} else {
						$productionObj->genre[0] = 'voorstelling';
					}
			}
			if (strpos($tags['genre'], '/') !== false) {
				$parts = explode(' ', $tags['genre']);
				$category = explode('/', $parts[0]);
				$productionObj->genre[0] = $parts[1];
				$productionObj->genre[1] = $category[1];
			}
			*/

			// Add link to production
			if (strpos($tags['link'], 'http') !== false) {
				if (substr_count($tags['link'], '/') > 3) {
					$productionObj->link = filter_var(trim($tags['link'].strtolower(str_replace(array(' ', '& '), array('-', ''), $productionObj->title))), FILTER_SANITIZE_URL);
				} else {
					$productionObj->link = filter_var(trim($tags['link']), FILTER_SANITIZE_URL);
				}
			} else {
				$productionObj->link = filter_var(trim($link), FILTER_SANITIZE_URL);
			}
			$productionObj->date->time = strtotime($time);
			$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
			if (array_key_exists('performers', $tags)) {
				$productionObj->performers = getPerformers($productionObj->link, $tags);
			}
			if (array_key_exists('cast', $tags) && strlen($production[$tags['cast']]) > 1) {
				$productionObj->performers = preg_split('/(, | i.s.m. )+/i', str_replace(array('/', 'e.a.'), array(',', ''), $production[$tags['cast']]));
			}

			//print_r($productionObj);

			// Push object to productions array
			// Exclude productions with irrelevant tags, title or sold out
			if (stripos($exclude, $productionObj->genre[0]) === false && stripos($exclude, $productionObj->genre[1]) === false && stripos($exclude, $productionObj->title) === false && stripos($productionObj->title, 'inleiding') === false && stripos($productionObj->title, 'workshop') === false && stripos($productionObj->title, 'afgelast') === false) {
				array_push($productions, $productionObj);
			}
		}
	}
	
	
	
} else {
	echo SimpleXLSX::parse_error();
}
?>