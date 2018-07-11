<?php
$location = array("venue"=>"Bimhuis","city"=>"Amsterdam");
$tags = 'title,"titel"; subtitle,"artiest"; genre,"genre"; category,"concert"';
$url = 'programma-bimhuis2018.xlsx';
$productionData = array();
$productions = array();
$columns = array();
include('../simplexlsx.php');

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
	
	// Create productions object from production data
	foreach ($productionData as $production) {
		$productionObj = new stdClass();
		
		// Define time and link variable
		$time = '';
		$link = '';
		
		foreach ($production as $data) {
			
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

			}
		}
		
		$productionObj->title = $production['titel'];
		$productionObj->subtitle = $production['artiest'];
		$productionObj->venue = $location['venue'];
		$productionObj->location = $location['city'];
		$productionObj->genre = listGenres($tags['genre']);
		if (count($productionObj->genre) <= 1) {
				if (strlen($tags['category']) > 1) {
					$productionObj->genre[0] = $tags['category'];
				} else {
					$productionObj->genre[0] = 'voorstelling';
				}
		}
		$productionObj->link = $link;
		$productionObj->date->time = strtotime($time);
		$productionObj->date->dateString = date('d-m-Y H:i', $productionObj->date->time);
		array_push($productions, $productionObj);
	}
	echo json_encode($productions);
	
	
	
} else {
	echo SimpleXLSX::parse_error();
}
?>