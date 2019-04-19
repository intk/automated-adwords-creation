<?php
/*
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>AutomateAdWords</title>
<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<!--<link rel="stylesheet" href="css/stylesheet.css">-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyAO8w_eT6U2io0zpPxSNLzQuur2aalzrUQ"></script>
<script>
	//Load target location autocomplete
	var autocomplete = new google.maps.places.Autocomplete($("input[name=targetLocation]")[0], {});
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var mapLocationLat = autocomplete.getPlace().geometry.location.lat();
        var mapLocationLong = autocomplete.getPlace().geometry.location.lng();
		var radius = $('select[name=targetRadius]').val();
		$('input[name=geoLocation]').val('('+radius+'km:'+mapLocationLat+':'+mapLocationLong+')')
	});

});
</script>
</head>
<body>
<?php
*/
session_start();
header('Content-Type: application/json');
include('includes/config.inc.php');
 header("Access-Control-Allow-Origin: *");

//load method for creating CSV
include('includes/methods/csv/csv.processor.php');
error_reporting(E_ALL);

// Determine if performance already exists 


// Parse ads templates from db
function parseTemplate($template) {
	$tplString = preg_replace("/\s+/", " ", $template);	
	// Get template blocks
	$blocks = preg_split('/(\w* { |\w* = )/', $tplString, -1, PREG_SPLIT_DELIM_CAPTURE);
	array_push($blocks, '');
	$blocks = array_chunk(array_slice($blocks, 1, -1, true), 2, false);
	
	// Iterate template blocks
	foreach($blocks as $key => $block) {
		$block[0] = trim(str_replace(array('{', ' ='), array('',''), $block[0]));
		$chunk = preg_split('/\[[0-9]\] /', $block[1]);
		//Iterate ad templates and assign them to types object
		foreach($chunk as $key => $val) {
			if (strlen($val) > 1) {
				$types[$block[0]][$key] = explode(', ', str_replace(array('}', '"'), array('',''), $val));
			}
		}
		// Reset keys of types object
		$types[$block[0]] = array_values($types[$block[0]]);
	
	}
	
	// Assign preposition for headings
	$types['prep'] = $types['prep'][0][0];
	if (strlen($types['placeholder'][0][0]) > 1) {
		$types['placeholder'] = $types['placeholder'][0][0];
	}
	
	return $types;
}

//Check if query string matches database records
if ($_GET['theater']) {

	//Get custom templates by theater ID
	$query = mysqli_query($connect, "SELECT * FROM theaters JOIN templates ON templates.theaterId = theaters.id WHERE theaters.alias LIKE '%".mysqli_real_escape_string($connect, $_GET['theater'])."%'");
	if (mysqli_num_rows($query) < 1) {
		// If no custom template is available, use default template
		$query = mysqli_query($connect, "SELECT * FROM theaters JOIN templates ON templates.theaterId = 0 WHERE theaters.alias LIKE '%".mysqli_real_escape_string($connect, $_GET['theater'])."%'");
	}
	
	if (mysqli_num_rows($query) >= 1) {
		$result = mysqli_fetch_array($query);
		
		// Assign template for ads
		$template = $result['content'];

		//Parse monthly season
		include('includes/scraper.inc.php');
		if ($_GET['month']) {
			$month = filter_var($_GET['month'], FILTER_SANITIZE_STRING);
		} else {
			$month = date('Y-m', strtotime('+1 month', time()));
		}
		
		// Filter on venue
		if ($_GET['venue']) {
			$venue = filter_var(str_replace('-', ' ', $_GET['venue']), FILTER_SANITIZE_STRING);
		} else {
			$venue = null;
		}
		$theater = new Theater($result['alias'], $month, $venue);
		$_SESSION['INTK-processID'] = base64_encode($theater->name.'_'.time());
	
		if ($_SESSION['INTK-processID']) {
		
			//Create new campaign object of each production
			include('includes/campaign/campaign.inc.php');
			if (isset($_GET['numberOnly'])) {
				$theater->productionsNumber = count($theater->productions);
			} else {

				$storedPerformances = array();
				$performances = $theater->productions;

				//Filter double performances
				foreach($theater->productions as $key => $item) {

					// Determine if performance exists
					$found = array_search(trim($item->title), $storedPerformances);
					if ($found !== false) {
						//Unset same performance
						unset($storedPerformances[$found]);
						unset($theater->productions[$found]);
					}

					// Add performance to storage
					$storedPerformances[$key] = trim($item->title);
				}


				// Rebase keys
				$tempPerformances = $theater->productions;
				$theater->productions = array();
				$iTheater = 0;
				foreach($tempPerformances as $value) {
					$theater->productions[$iTheater] = $value;
					$iTheater++;
				}


				foreach($theater->productions as $key => $item) {

					$campaign = new Campaign($item, $template);
					$campaign->createAdgroup();
					$theater->productions[$key]->campaign = $campaign;
					$theater->csvOutput = createCSV($theater->name, $month, $result['budget'], $result['targetLocation'], 'enabled', $theater->productions[$key]);
					
					//Import performance data to database
					$select = mysqli_query($connect, "SELECT * FROM performances WHERE theaterId=".$result['id']." AND title='".$item->title."' AND performanceDate = '".$item->date->time."'");
					$selectResult = mysqli_fetch_assoc($select);
					if (count($selectResult) < 1) {
						$import = mysqli_query($connect, "INSERT INTO performances (theaterId, title, subtitle, genre, performanceDate, creationDate, link) VALUES (".$result['id'].", '".$item->title."', '".$item->subtitle."', '".implode(';', $item->genre)."', '".$item->date->time."', '".time()."', '".$item->link."')");

					}

					// Add performance to storage
					$storedPerformances[$key] = $item->title;
				}



			}
			echo json_encode($theater);

		}
	}
}
?>
<?php
/*
</body>
</html>
*/