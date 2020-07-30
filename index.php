<?php 
header('Content-Type: text/html');
include('includes/config.inc.php'); 
$theater['success'] = false;

//Set client character set to utf-8
mysqli_query($connect, "SET NAMES 'utf8'");

$query = mysqli_query($connect, "SELECT * FROM theaters WHERE alias='".mysqli_real_escape_string($connect, $_GET['client'])."'");
if (mysqli_num_rows($query) >= 1) {
	$result = mysqli_fetch_array($query);

	$queryString = '';

	if ($_GET['client'] && !$_GET['month']) {
		$queryString = "client=".$result['alias'];
		$month = date('m', strtotime('+1 month', time()));
	}
	if ($_GET['client'] && $_GET['month']) {
		$queryString = "client=".$result['alias']."&month=".$_GET['month'];
		$month = explode('-', $_GET['month'])[1];
	}
	if ($_GET['client'] && $_GET['month'] && $_GET['splitKeywords']) {
		$queryString = "client=".$result['alias']."&month=".$_GET['month']."&splitKeywords=".$_GET['splitKeywords'];
		$month = explode('-', $_GET['month'])[1];
	}

	if ($_GET['lang']) {
		$queryString .= "&lang=".$_GET['lang'];
	}

	echo "<script>var queryString = '".$queryString."';</script>";
	
	$theater['name'] = $result['name'];
	$months = array("January", "February", "March", "April", "May", "June", "July", "Augustus", "September", "October", "November", "December");
	$theater['month'] = $months[$month-1];
	$theater['success'] = true;

}
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>Adwords Automation <?php if ($theater['success']) { echo '&#124; '.$theater['name']; } ?></title>
<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/stylesheet.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<!--<script src="https://maps.google.com/maps/api/js?libraries=places&key=AIzaSyAO8w_eT6U2io0zpPxSNLzQuur2aalzrUQ"></script>-->
	<script src="js/load.js"></script>
<script>
	/*//Load target location autocomplete
	var autocomplete = new google.maps.places.Autocomplete($("input[name=targetLocation]")[0], {});
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var mapLocationLat = autocomplete.getPlace().geometry.location.lat();
        var mapLocationLong = autocomplete.getPlace().geometry.location.lng();
		var radius = $('select[name=targetRadius]').val();
		$('input[name=geoLocation]').val('('+radius+'km:'+mapLocationLat+':'+mapLocationLong+')');
	});

});*/
</script>
</head>
<body>
<div id="csvInput"></div>
<div class="wrapper">
<?php

if (!$theater['success']) {
	echo "<span>This theater doesn't exsist.</span>";
} else {
?>
<form action="#" name="getCampaigns" method="post">
<div class="loader">
    <svg class="circular-loader"viewBox="25 25 50 50" >
      <circle class="loader-path" cx="50" cy="50" r="20" fill="none" />
    </svg>
</div>
<input type="submit" value="Create <?php if (strlen($theater['month']) > 1) {echo $theater['month'].' ';} ?>campaigns for <?php echo $theater['name']; ?>">
<input type="button" data="" class="copyOutput" value="Copy campaigns">
</form>
<?php
}
?>
</div>
</body>
</html>