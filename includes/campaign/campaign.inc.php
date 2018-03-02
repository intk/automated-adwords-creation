<?php

//Contains name, date, target location, sitelink extentions, AdGroups, Ads, keywords
class Campaign {
	private $title;
	private $subtitle;
	private $venue;
	private $location;
    public function __construct($production) {
		//Parse campaign info
		$this->title = $this->trimStr($production->title);
		$this->subtitle = $production->subtitle;
		$this->name = trim(ucfirst($production->genre).': '.$this->title.' - '.$production->subtitle);
		$this->venue = $production->venue;
		$this->location = $production->location;
		$this->genre = trim($production->genre);
		$itemDate = $production->date->time;
		$this->date->GaDate = $this->formatDate($itemDate)[0];	
		$this->date->AdDate = $this->formatDate($itemDate)[1];
		//$this->date->dateString = $production->date->dateString;
    }
	
	private function trimStr($string) {
		return preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'’&-]+/u", " ", $string)));
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
			if ($heading['type'] == 'artist') {
				$tempAdgroup = preg_split('(,| en | /)', $heading['value']);
			} else {
				$tempAdgroup[0] = $heading['value'];
			}
						
			foreach($tempAdgroup as $gkey => $gval) {
				//Remove numbers, special characters (excl. '’&-) and unnecessary mentions between brackets
				$this->adgroup[$i]->name = preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'’&-]+/u", " ", $gval)));
				$this->adgroup[$i]->type = $heading['type'];
				
				$this->adgroup[$i]->ad = $this->createAds(trim($this->adgroup[$i]->name), $this->adgroup[$i]->type, $this->trimArtist($this->subtitle));
				$this->adgroup[$i]->keywords = $this->createKeywords(trim($this->adgroup[$i]->name));
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
	
	private function trimArtist($artist) {
		$tempArtist = preg_split('(,| en | /| Producties)', $artist);
		return trim($tempArtist[0]);
	}

	
	private function truncate($text, $length) {
	   $length = abs((int)$length);
	   if(strlen($text) > $length) {
		  $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1', $text);
	   }
	   return($text);
	}
	
	private function createAds($title, $type, $artist) {
		$ad = array();
		$pLabel = new stdClass();
		$pLabel->toneel = array('toneel', 'toneel');
		$pLabel->cabaret = array('cabaret', 'cabaret');
		$pLabel->musical = array('musical', 'een musical');
		$pLabel->familievoorstelling = array("voorstelling", "een voorstelling");
		$pLabel->dans = array('dans', 'een danssshow');
		$pLabel->concert = array('concert', 'een concert');
		$pLabel->muziek = array('concert', 'een concert');
		$pLabel->klassiek = array('concert', 'een concert');
		
		$genre = $this->genre;
	
		if ($pLabel->$genre != null) {
			$tLabel = $pLabel->$genre;
		} else {
			$tLabel = array('voorstelling', 'een voorstelling');
		}
		
		if ($type == 'title') {
			
			//Templates Title ad
			
			//Check if titles length is more than 30 characters. Depend heading values on that
			if (strlen($title) > 30) {
				//Divide heading1 and heading2 in 2 elements
				$heading[0] = array($artist.' - '.ucfirst($this->genre), $this->date->AdDate.' in '.$this->location);
				$heading[1] = array(ucfirst($this->genre).' - '.$artist, $this->venue);
				$heading[2] = array($artist.' - '.ucfirst($this->genre), $this->venue);
			} else {
				//Divide heading1 and heading2 in 2 elements
				$heading[0] = array($title, $this->date->AdDate.' in '.$this->location);
				$heading[1] = array($title, $artist);
				$heading[2] = array($title, $this->venue);
			}
			
			$template[0] = array(
				"Naar ".$tLabel[1]." in ".$this->location."? Koop nu mijn kaarten voor ".$title.".",
			);
			$template[1] = array(
				"Kom naar ".$title." in ".$this->venue.". Bestel nu mijn tickets voor ".$this->date->AdDate,
			);
			$template[2] = array(
				"Geniet van ".$title." door ".$artist.". Koop nu tickets voor ".$this->date->AdDate,
			);
			
		} 
		if ($type == 'artist') {
			
			$performance = trim($this->title);
			
			//Templates Artist ad
			$heading[0] = array($title, $this->date->AdDate.' in '.$this->location);
			$heading[1] = array($title, $performance);
			$heading[2] = array($title, $this->venue);
			
			$template[0] = array(
				"Naar ".$tLabel[1]." in ".$this->location."? Koop nu mijn kaarten voor ".$title.".",
			);
			$template[1] = array(
				"Kom naar ".$title." in ".$this->venue.". Bestel nu mijn tickets voor ".$this->date->AdDate,
			);
			$template[2] = array(
				"Geniet van ".$title." in ".$performance.". Koop nu tickets voor ".$this->date->AdDate,
			);
		}
			
			
			foreach($template as $key => $tpl) {
				$ad[$key]->heading[0] = $heading[$key][0];
				$ad[$key]->heading[1] = $heading[$key][1];
				$ad[$key]->description = $template[$key][0];
				
				$pathString = strtolower(trim(preg_replace('/(de | een | en | het )/', ' ', $title)));
				
				//Check if pathString length <= 15 characters
				if (strlen($pathString) <= 15) {
					$ad[$key]->path[0] = strtolower($this->genre);
					$ad[$key]->path[1] = str_replace(' ', '-', $pathString);
				}
				
				//Check if pathstring has more than 15 characters and split the words to the path fields
				else if (strlen($pathString) > 15) {
					$ad[$key]->path[0] = str_replace(' ', '-', trim($this->truncate($pathString, 15)));
					$ad[$key]->path[1] = str_replace(' ', '-', trim($this->truncate(substr($pathString, strlen($this->truncate($pathString, 15)), 30), 15)));
				}
			}
			
		
		return $ad;
	}
	
	private function createKeywords($name) {
			$placements = new stdClass();
			$placements->toneel = array('voorstelling', 'toneel', 'toneelstuk', 'tickets', 'theater');
			$placements->cabaret = array('cabaret', 'show', 'theater', 'tickets');
			$placements->musical = array('show', 'theater');
			$placements->college = array('college', 'theater');
			$placements->familie = array("familie", "theater");
			$placements->jeugd = array("familie", "theater");
			$placements->dans = array('dans', 'theater');
			$placements->serie = array('voorstelling', 'theater');
		    $placements->concert = array('concert', 'theater');
			$placements->show = array('concert', 'theater');
			$placements->muziek = array('concert', 'theater');
			$placements->klassiek = array('concert', 'theater');
			$placements->overig = array('voorstelling', 'theater');
			$placements->opera = array('opera', 'theater');
			$placements->specials = array('voorstelling', 'theater');
			$placements->circus = array('circus', 'theater');
			$placements->entertainment = array('show', 'theater');
			$genre = $this->genre;
		
			$keywordList = array();
		
			//Loop keyword list
			foreach($placements as $keyWord => $keyValue) {
				//Check if genre has keyword array
				if (stripos($genre, $keyWord) !== false) {
						foreach($keyValue as $tempKey => $tempVal) {
							//Add keywords
								if (stripos($name, $tempVal) != false) {
									array_push($keywordList, strtolower($name));
								} else {
									array_push($keywordList, strtolower($name).' '.strtolower($tempVal));
								}
							//}
							
							}
							
				}
					//}
			}
		
		return $keywordList;
	}
}


?>