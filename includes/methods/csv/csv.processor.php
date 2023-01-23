<?php
// Encode special characters before output
include(dirname(__FILE__).'/encoding.inc.php');
use \ForceUTF8\Encoding;

function createCSV($theatre, $monthYear, $dailybudget, $location, $status, $package) {

$data = array (
	"campaign" => $package->campaign->name,
	"labels" => "Automation;".implode(';', $package->campaign->genre),
	"campaignDailyBudget" => str_replace(',', '.', $dailybudget),
	"campaignType" => "Search Network only",
	"networks" => "Google search",
	"languages" => $package->campaign->language,
	"bidStrategyType" => "Maximize conversions",
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
	
	/*
	//SITELINK
	"DevicePreference" => "",
	"LinkText" => "",
	"DescriptionLine1" => "",
	"DescriptionLine2" => "",
	"FeedName" => "",
	"PlatformTargeting" => "",
	*/
	
	//CALL
	/*"PhoneNumber" => "",
	"CountryOfPhone" => "",
	"CallReporting" => "",
	"ConversionAction" => "",*/

	"Headline1" => "",
	"Headline1Position" => "",
	"Headline2" => "",
	"Headline2Position" => "",
	"Headline3" => "",
	"Headline3Position" => "",
	"Headline4" => "",
	"Headline4Position" => "",
	"Headline5" => "",
	"Headline5Position" => "",
	"Headline6" => "",
	"Headline6Position" => "",
	"Headline7" => "",
	"Headline7Position" => "",
	"Headline8" => "",
	"Headline8Position" => "",
	"Headline9" => "",
	"Headline9Position" => "",
	"Headline10" => "",
	"Headline10Position" => "",
	"Headline11" => "",
	"Headline11Position" => "",
	"Headline12" => "",
	"Headline12Position" => "",
	"Headline13" => "",
	"Headline13Position" => "",
	"Headline14" => "",
	"Headline14Position" => "",
	"Headline15" => "",
	"Headline15Position" => "",
	"DescriptionLine1" => "",
	"DescriptionLine1Position" => "",
	"DescriptionLine2" => "",
	"DescriptionLine2Position" => "",
	"DescriptionLine3" => "",
	"DescriptionLine3Position" => "",
	"DescriptionLine4" => "",
	"DescriptionLine4Position" => "",
	"path1" => "",
	"path2" => "",
	"campaignStatus" => $status
);

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
		$adArr[array_search("Headline1Position", array_keys($data))] = "-";
		$adArr[array_search("Headline2", array_keys($data))] = $ad->heading[1];
		$adArr[array_search("Headline2Position", array_keys($data))] = "-";
		$adArr[array_search("Headline3", array_keys($data))] = $ad->heading[2];
		$adArr[array_search("Headline3Position", array_keys($data))] = "-";
		$adArr[array_search("Headline4", array_keys($data))] = $ad->heading[3];
		$adArr[array_search("Headline4Position", array_keys($data))] = "-";
		$adArr[array_search("Headline5", array_keys($data))] = $ad->heading[4];
		$adArr[array_search("Headline5Position", array_keys($data))] = "-";
		$adArr[array_search("Headline6", array_keys($data))] = $ad->heading[5];
		$adArr[array_search("Headline6Position", array_keys($data))] = "-";
		$adArr[array_search("Headline7", array_keys($data))] = $ad->heading[6];
		$adArr[array_search("Headline7Position", array_keys($data))] = "-";
		$adArr[array_search("Headline8", array_keys($data))] = $ad->heading[7];
		$adArr[array_search("Headline8Position", array_keys($data))] = "-";
		$adArr[array_search("Headline9", array_keys($data))] = $ad->heading[8];
		$adArr[array_search("Headline9Position", array_keys($data))] = "-";
		$adArr[array_search("Headline10", array_keys($data))] = $ad->heading[9];
		$adArr[array_search("Headline10Position", array_keys($data))] = "-";
		$adArr[array_search("Headline11", array_keys($data))] = $ad->heading[10];
		$adArr[array_search("Headline11Position", array_keys($data))] = "-";
		$adArr[array_search("Headline12", array_keys($data))] = $ad->heading[11];
		$adArr[array_search("Headline12Position", array_keys($data))] = "-";
		$adArr[array_search("Headline13", array_keys($data))] = $ad->heading[12];
		$adArr[array_search("Headline13Position", array_keys($data))] = "-";
		$adArr[array_search("Headline14", array_keys($data))] = $ad->heading[13];
		$adArr[array_search("Headline14Position", array_keys($data))] = "-";
		$adArr[array_search("Headline15", array_keys($data))] = $ad->heading[14];
		$adArr[array_search("Headline15Position", array_keys($data))] = "-";
		$adArr[array_search("path1", array_keys($data))] = $ad->path[0];
		$adArr[array_search("path2", array_keys($data))] = $ad->path[1];

		// Split description text over 2 lines if first line has more than 90 characters
		if ((strlen($ad->description[0]) > 90 && strpos($ad->description[0], '{KeyWord:') === false)  || (strpos($ad->description[0], '{KeyWord:') > 1 && strlen($ad->description[0]) > 100)) {
			$descriptionParts = preg_split( "/(\.|!)/", $ad->description[0], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$adArr[array_search("DescriptionLine1", array_keys($data))] = $descriptionParts[0].$descriptionParts[1];
			$adArr[array_search("DescriptionLine2", array_keys($data))] = $descriptionParts[2].$descriptionParts[3];
		} else {
			// If first description line has <= characters, use default template for both description lines
			$adArr[array_search("DescriptionLine1", array_keys($data))] = $ad->description[0];
			$adArr[array_search("DescriptionLine2", array_keys($data))] = $ad->description[1];
		}
		$adArr[array_search("DescriptionLine3", array_keys($data))] = $ad->description[2];
		$adArr[array_search("DescriptionLine4", array_keys($data))] = $ad->description[3];
		$adArr[array_search("DescriptionLine1Position", array_keys($data))] = "-";
		$adArr[array_search("DescriptionLine2Position", array_keys($data))] = "-";
		$adArr[array_search("DescriptionLine3Position", array_keys($data))] = "-";
		$adArr[array_search("DescriptionLine4Position", array_keys($data))] = "-";
			


		/*
		
		if (strlen($ad->description) > 90) {
			$descriptionParts = preg_split( "/(\.|!)/", $ad->description, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$adArr[array_search("DescriptionLine1", array_keys($data))] = $descriptionParts[0].$descriptionParts[1];
			$adArr[array_search("DescriptionLine2", array_keys($data))] = $descriptionParts[2].$descriptionParts[3];
		} else {
			$adArr[array_search("DescriptionLine1", array_keys($data))] = $ad->description;
		}
		*/
		
		$list[$i+count($list)] = $adArr;

		/*if ($package->campaign->adgroup[$i]->name == "Performers" || $package->campaign->adgroup[$i]->type == "performer" && $key >= 2) {*/
		$list[$i+count($list)] = $adArr;
		//}
	}
}

//Add location
$LocArr = $temp;
$LocArr[array_search("campaign", array_keys($data))] = $data['campaign'];
$LocArr[array_search("campaignStatus", array_keys($data))] = $data['campaignStatus'];
if ($location == "Nederland" || $location == "Netherlands") {
	$LocArr[array_search("ID", array_keys($data))] = "2528";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "25600000";
}
if (stripos($location, "Belg") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2056";
	$LocArr[array_search("Location", array_keys($data))] = "België";
	$LocArr[array_search("Reach", array_keys($data))] = "8540000";
}
if (stripos($location, "Spain") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2724";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "38300000";
}
if (stripos($location, "Luxembourg") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2442";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "1090000";
}
if (stripos($location, "Slovenia") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2705";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "2270000";
}
if (stripos($location, "Poland") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2616";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "27100000";
}
if (stripos($location, "Sweden") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2752";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "8110000";
}
if (stripos($location, "Finland") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2246";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "5150000";
}
if (stripos($location, "Italy") !== false) {
	$LocArr[array_search("ID", array_keys($data))] = "2380";
	$LocArr[array_search("Location", array_keys($data))] = $location;
	$LocArr[array_search("Reach", array_keys($data))] = "48300000";
}

 else {
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
	$fields = Encoding::toUTF8($fields);
    fputcsv($fp, $fields);
}
fclose($fp);
	return 'campaigns/'.$_SESSION['INTK-processID'].'.csv';
}
