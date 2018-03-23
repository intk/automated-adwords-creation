<?php
function createCSV($theatre, $monthYear, $dailybudget, $location, $status, $package) {
$data = array (
	"campaign" => $package->campaign->name,
	"labels" => "",
	"campaignDailyBudget" => str_replace(',', '.', $dailybudget),
	"campaignType" => "Search Network only",
	"networks" => "Google search",
	"languages" => "nl",
	"bidStrategyType" => "Manual CPC",
	"bidStrategyName" => "",
	"enhancedCPC" => "disabled",
	"CPABid" => "0.00",
	"startDate" => date('d-m-y'),
	"endDate" => date('d-m-y', $package->date->time),
	"adSchedule" => "[]",
	"adRotation" => "Optimize for clicks",
	"deliveryMethod" => "standard",
	"targetingMethod" => "Location of presence or Area of interest",
	"exclusionMethod" => "Location of presence or Area of interest",
	"AdGroup" => "",
	"MaxCPC" => "",
	"keyword" => "",
	"CriterionType" => "",
	"ID" => "",
	"Location" => "",
	"Reach" => "",
	"FinalURL" => "",
	
	//SITELINK
	"DevicePreference" => "",
	"LinkText" => "",
	"DescriptionLine1" => "",
	"DescriptionLine2" => "",
	"FeedName" => "",
	"PlatformTargeting" => "",
	
	//CALL
	/*"PhoneNumber" => "",
	"CountryOfPhone" => "",
	"CallReporting" => "",
	"ConversionAction" => "",*/
	
	"Description" => "",
	"Headline1" => "",
	"Headline2" => "",
	"path1" => "",
	"path2" => "",
	"campaignStatus" => $status
);
	
/*
$siteLinks = array(
	array(
	    "FinalURL" => "https://www.dekom.nl/zakelijk/",
		"DevicePreference" => "All",
		"LinkText" => "KOM in zaken",
		"DescriptionLine1" => "Ontdek hier de mogelijkheden",
		"DescriptionLine2" => "voor zakelijke events bij DE KOM.",
		"FeedName" => "Main sitelink feed",
		"PlatformTargeting" => "All"
	),
	array(
	    "FinalURL" => "https://www.dekom.nl/agenda/kaartverkoop/",
		"DevicePreference" => "All",
		"LinkText" => "Kaartverkoop",
		"DescriptionLine1" => "Lees hier meer informatie over",
		"DescriptionLine2" => "kaartverkoop bij DE KOM.",
		"FeedName" => "Main sitelink feed",
		"PlatformTargeting" => "All"
	),
	array(
	    "FinalURL" => "https://www.dekom.nl/informatie/contact/",
		"DevicePreference" => "All",
		"LinkText" => "Contact",
		"DescriptionLine1" => "Adres, telefoonnummers en het",
		"DescriptionLine2" => "contactformulier van DE KOM.",
		"FeedName" => "Main sitelink feed",
		"PlatformTargeting" => "All"
	),
	array(
	    "FinalURL" => "https://www.dekom.nl/cursussen/",
		"DevicePreference" => "All",
		"LinkText" => "Cursusprogramma",
		"DescriptionLine1" => "Ontdek het uitgebreide cursusaanbod",
		"DescriptionLine2" => "met muziek, toneel, dans en meer.",
		"FeedName" => "Main sitelink feed",
		"PlatformTargeting" => "All"
	)
	);
	*/

$temp = array(); 
for ($i = 0; $i < count($data); $i++) {
    array_push($temp, "");
}

//Create array list
$list[0] = array();
$list[1] = array();

//add columns and first data row
foreach ($data as $key => $value) {
	if (!file_exists('campaigns/'.$_SESSION['INTK-processID'].'.csv')) {
		array_push($list[0], $key);
		array_push($list[1], $value);
	} else {
		array_push($list[0], $value);

	}
}

//add AdGroups
for ($i = 0; $i < count($package->campaign->adgroup); $i++) {
	$tempArr = $temp;
	$tempArr[array_search("campaign", array_keys($data))] = $data['campaign'];
	$tempArr[array_search("campaignStatus", array_keys($data))] = $data['campaignStatus'];
	$tempArr[array_search("AdGroup", array_keys($data))] = $package->campaign->adgroup[$i]->name;
	$tempArr[array_search("MaxCPC", array_keys($data))] = "2.00";
	$list[$i+count($list)] = $tempArr;
	//add keywords
	foreach($package->campaign->adgroup[$i]->keywords as $keywords) {
		$tempArr[array_search("keyword", array_keys($data))] = $keywords;
		$tempArr[array_search("CriterionType", array_keys($data))] = "Broad";
		$list[$i+count($list)] = $tempArr;
	}
	//add ad
	foreach($package->campaign->adgroup[$i]->ad as $key => $ad) {
		$adArr = $temp;
		$adArr[array_search("campaign", array_keys($data))] = $data['campaign'];
		$adArr[array_search("campaignStatus", array_keys($data))] = $data['campaignStatus'];
		$adArr[array_search("AdGroup", array_keys($data))] = $package->campaign->adgroup[$i]->name;
		$adArr[array_search("FinalURL", array_keys($data))] = $package->link;
		$adArr[array_search("Headline1", array_keys($data))] = $ad->heading[0];
		$adArr[array_search("Headline2", array_keys($data))] = $ad->heading[1];
		$adArr[array_search("path1", array_keys($data))] = $ad->path[0];
		$adArr[array_search("path2", array_keys($data))] = $ad->path[1];
		$adArr[array_search("Description", array_keys($data))] = $ad->description;
		$list[$i+count($list)] = $adArr;
		if ($package->campaign->adgroup[$i]->name == "Performers" && $key == 2) {
			$list[$i+count($list)] = $adArr;
		}
	}
}

//Add location
$LocArr = $temp;
$LocArr[array_search("campaign", array_keys($data))] = $data['campaign'];
$LocArr[array_search("campaignStatus", array_keys($data))] = $data['campaignStatus'];
if ($location == "Nederland") {
	$LocArr[array_search("ID", array_keys($data))] = "2528";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "20000000";
} else {
	$LocArr[array_search("Location", array_keys($data))] = $location;
}
$list[count($list)+1] = $LocArr;
	
/*
foreach($siteLinks as $sitelink) {
	$slArr = $temp;
	$slArr[array_search("campaign", array_keys($data))] = $data['campaign'];
	$slArr[array_search("campaignStatus", array_keys($data))] = $data['campaignStatus'];
	foreach ($sitelink as $slKey => $slVal) {
		$slArr[array_search($slKey, array_keys($data))] = $slVal;
	}
	$list[count($list)+1] = $slArr;
}
*/
	/*
foreach ($data as $key => $value) {
	if (!file_exists('campaigns/campaigns-'.str_replace(' ', '_', $theatre).'-'.$monthYear.'.csv')) {
		array_push($list[0], $key);
		array_push($list[1], $value);
	} else {
		array_push($list[0], $value);
	}
}*/

$fp = fopen('campaigns/'.$_SESSION['INTK-processID'].'.csv', 'a');

foreach ($list as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);
	return '<a href="campaigns/'.$_SESSION['INTK-processID'].'.csv" target="_blank">Open CSV File</a>';
}
