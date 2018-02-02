<?php

//Contains name, date, target location, sitelink extentions, AdGroups, Ads, keywords
class Campaign {
	private $title;
	private $subtitle;
    public function __construct($production) {
		//Parse campaign info
		$this->title = $production->title;
		$this->subtitle = $production->subtitle;
		$this->name = trim($production->genre.': '.$this->title.' - '.$production->subtitle);
		$this->genre = $production->genre;
		$itemDate = $production->date->time;
		$this->date->GaDate = $this->formatDate($itemDate)[0];	
		$this->date->AdDate = $this->formatDate($itemDate)[1];
		//$this->date->dateString = $production->date->dateString;
    }
	//Format timestamp to different date strings
	private function formatDate($time) {
		$monthNL = array('jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec');
		$monthEN = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
		$day = date('d', $time);
		if ($day < 10) { $day = substr($day, 1); }
		$output[0] = date('M d, Y', $time);
		$output[1] = $day.' '.str_ireplace($monthEN, $monthNL, date('M', $time)).'.';
		return $output;
	}
	
	public function createAdgroup() {
		$tempHeadings = array();
		$adGroups = array();
		//Check whether title values exist or not and push to headings array
		if (strlen($this->title)>1) { array_push($tempHeadings, array("value"=>$this->title,"type"=>"title")); }
		if (strlen($this->subtitle)>1) { array_push($tempHeadings, array("value"=>$this->subtitle,"type"=>"artist")); }
		
		//Split headings array to seperate adgroups
		$i = 0;
		foreach ($tempHeadings as $key => $heading) {
			$tempAdgroup = preg_split('/(,|&| en )/', $heading['value']);
			foreach($tempAdgroup as $gkey => $gval) {
				$this->adgroup[$i]->name = trim(preg_replace('/[^a-z]+/i', ' ', $gval));
				$this->adgroup[$i]->type = $heading['type'];
				$i++;
			} 
		}

		/*
		foreach($tempAdgroup as $gkey => $gval) {
				$this->adgroup[$gkey]->name = trim($gval);
			}
			*/
		//$this->adgroup[0]->name = $this->title;
	}
}


?>