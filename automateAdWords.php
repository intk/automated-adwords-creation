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
//load method for creating CSV
include('includes/methods/csv/csv.processor.php');
error_reporting(E_ALL);
	
//Check if query string matches database records
if ($_GET['theater']) {
	$query = mysqli_query($connect, "SELECT * FROM theaters WHERE alias LIKE '%".mysqli_real_escape_string($connect, $_GET['theater'])."%'");
	if (mysqli_num_rows($query) >= 1) {
		$result = mysqli_fetch_array($query);

		//Parse monthly season
		include('includes/scraper.inc.php');
		if ($_GET['month']) {
			$month = $_GET['month'];
		} else {
			$month = date('Y-m', strtotime('+1 month', time()));
		}
		$theater = new Theater($result['alias'], $month);
		$_SESSION['INTK-processID'] = base64_encode($theater->name.'_'.time());
	
		if ($_SESSION['INTK-processID']) {
		
			//Create new campaign object of each production
			include('includes/campaign/campaign.inc.php');
			if ($_GET['numberOnly']) {
				$theater->productionsNumber = count($theater->productions);
			} else {
				foreach($theater->productions as $key => $item) {
					$campaign = new Campaign($item);
					$campaign->createAdgroup();
					$theater->productions[$key]->campaign = $campaign;
					$theater->csvOutput = createCSV($theater->name, $month, $result['budget'], $result['targetLocation'], 'paused', $theater->productions[$key]);
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