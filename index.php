<?php 
include('includes/config.inc.php'); 
$theater['success'] = false;
$query = mysqli_query($connect, "SELECT * FROM theaters WHERE alias='".mysqli_real_escape_string($connect, $_GET['theater'])."'");
if (mysqli_num_rows($query) >= 1) {
	$result = mysqli_fetch_array($query);

	if ($_GET['theater'] && !$_GET['month']) {
		echo "<script>var queryString = 'theater=".$result['alias']."'; </script>";
		$month = date('m', strtotime('+1 month', time()));
	}
	if ($_GET['theater'] && $_GET['month']) {
		echo "<script> var queryString = 'theater=".$result['alias']."&month=".$_GET['month']."'; </script>";
		$month = explode('-', $_GET['month'])[1];
	}
	
	$theater['name'] = $result['name'];
	$months = array("January", "February", "March", "April", "May", "June", "July", "Augustus", "September", "October", "November", "December");
	$theater['month'] = $months[$month-1];
	$theater['success'] = true;

}
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
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
<input type="submit" value="Create <?php echo $theater['month']; ?> campaigns for <?php echo $theater['name']; ?>">
<input type="button" data="" class="copyOutput" value="Copy campaigns">
</form>
<?php
}
?>
</div>
</body>
</html>