<?php

//Contains name, date, target location, sitelink extentions, AdGroups, Ads, keywords
class Campaign {
	private $title;
	private $venue;
	private $location;
	private $performers;
    public function __construct($production) {
		//Parse campaign info
		$itemDate = $production->date->time;
		$this->title = $this->trimStr($production->title);
		$this->subtitle = $this->trimStr($production->subtitle);
		//Campaign name format [[YYYY-MM-DD]] [performance] - [performer]
		$this->name = trim('['.$this->formatDate($itemDate)[3].'] '.$this->title.' - '.$production->subtitle);
		$this->venue = $production->venue;
		$this->location = $production->location;
		$this->genre = $production->genre;
		$this->performers = $production->performers;
		$this->date->GaDate = $this->formatDate($itemDate)[0];	
		$this->date->AdDate = $this->formatDate($itemDate)[1];
		$this->date->AdDateFull = $this->formatDate($itemDate)[2];
		//$this->date->dateString = $production->date->dateString;
    }
	
	private function trimStr($string) {
		//Replacements for unnecessary abbreviations
		$replace = array('e.a.', 'e.v.a.');
		$replacement = array('', '');
		return str_replace('+', '', preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'’&,-]+/u", " ", str_replace($replace, $replacement, $string)))));
	}
	
	//Format timestamp to different date strings
	private function formatDate($time) {
		$monthNL = array('jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec');
		$monthNLFull = array('januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december');
		$monthEN = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
		$day = date('d', $time);
		if ($day < 10) { $day = substr($day, 1); }
		$output[0] = date('M d, Y', $time);
		$output[1] = $day.' '.str_ireplace($monthEN, $monthNL, date('M', $time)).'.';
		$output[2] = $day.' '.str_ireplace($monthEN, $monthNLFull, date('M', $time));
		$output[3] = date('Y-m-d', $time);
		return $output;
	}
		
	public function createAdgroup() {
		$tempHeadings = array();
		$adGroups = array();
		//Check whether title values exist or not and push to headings array
		if (strlen($this->title)>1) { array_push($tempHeadings, array("value"=>$this->title,"type"=>"title")); }
		if (strlen($this->subtitle)>1) { array_push($tempHeadings, array("value"=>$this->subtitle,"type"=>"artist")); }
		if ($this->performers !== false) { array_push($tempHeadings, array("value"=>$this->performers,"type"=>"multiple-artists")); }

		
		//Split headings array to seperate adgroups
		$i = 0;
		foreach ($tempHeadings as $key => $heading) {
			if ($heading['type'] == 'artist') {
				$tempAdgroup = $this->trimArtist($heading['value']);
			} 
			else {
				$tempAdgroup[0] = $heading['value'];
			}
			
			if ($heading['type'] == 'multiple-artists') {
				// Create adGroup for multiple artists (keyword insertion)
				foreach($tempAdgroup as $gkey => $gval) {
					//Remove numbers, special characters (excl. '’&-) and unnecessary mentions between brackets
					$this->adgroup[$i]->name = 'Performers';

					//Check if misplaced dash (-) occurs in performance title
					if (strpos($this->title, '-') == (strlen($this->title) -1)) {
						$this->title = str_replace('-', '', $this->title);
					}
					$this->adgroup[$i]->type = $heading['type'];

					$this->adgroup[$i]->ad = $this->createAds(trim($this->adgroup[$i]->name), $this->adgroup[$i]->type, $this->trimArtist($this->subtitle));
					$this->adgroup[$i]->keywords = $this->createKeywords($this->performers,  $this->adgroup[$i]->type);
				}
				$i++;
			} else {
			
				// Create adGroup for single artist / performance
				foreach($tempAdgroup as $gkey => $gval) {
					//Remove numbers, special characters (excl. '’&-) and unnecessary mentions between brackets
					$this->adgroup[$i]->name = ucfirst(preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'’&-]+/u", " ", $gval))));

					//Check if misplaced dash (-) occurs in campaign name
					if (strpos($this->adgroup[$i]->name, '-') == (strlen($this->adgroup[$i]->name) -1)) {
						$this->adgroup[$i]->name = str_replace('-', '', $this->adgroup[$i]->name);
					}
					//Check if misplaced dash (-) occurs in performance title
					if (strpos($this->title, '-') == (strlen($this->title) -1)) {
						$this->title = str_replace('-', '', $this->title);
					}
					$this->adgroup[$i]->type = $heading['type'];

					$this->adgroup[$i]->ad = $this->createAds(trim($this->adgroup[$i]->name), $this->adgroup[$i]->type, $this->trimArtist($this->subtitle));
					$this->adgroup[$i]->keywords = $this->createKeywords(trim($this->adgroup[$i]->name),  $this->adgroup[$i]->type);
					$i++;
				}
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
		// Check whether artist value consits of multiple performers that have to be splitted into their own adgroup
		if (strpos($artist, ',') !== false || (strpos($artist, ',') < strpos($artist, ' en '))) {
			$tempArtist = preg_split('(,| en )', $artist);
		}
		else if (strpos($artist, ' en ') !== false && strpos($artist, '&') !== false) {
			$tempArtist = preg_split('(,| & )', $artist);
		}
		else if (strpos($artist, ' en ') === false && strpos($artist, ' & ') !== false && strlen($artist) > 30) {
			$tempArtist = explode(' & ', $artist);
		}
		else if (strpos($artist, ' and ') !== false && strlen($artist) > 30) {
			$tempArtist = explode(' and ', $artist);
		}
		else if (strpos($artist, ' - ') !== false) {
			$tempArtist = explode(' - ', $artist);
		} else {
			$tempArtist[0] = $artist;
		}
		return $tempArtist;
	}

	
	private function truncate($text, $length) {
	   $length = abs((int)$length);
	   if(strlen($text) > $length) {
		  $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1', $text);
	   }
	   return($text);
	}
	
	//Sort string by length in ascending order
	private function sortByLength($a,$b) {
    	return strlen($a)-strlen($b);
	}
	
	private function createAds($title, $type, $artist) {
		//Get first artist in the list
		$artist = $artist[0];
		$ad = array();
		$pLabel = new stdClass();
		$pLabel->toneel = array('toneel', 'toneel');
		$pLabel->cabaret = array('cabaret', 'cabaret');
		$pLabel->musical = array('musical', 'een musical');
		$pLabel->familievoorstelling = array("voorstelling", "een voorstelling");
		$pLabel->jeugd = array("toneel", "toneel");
		$pLabel->dans = array('dans', 'een danssshow');
		$pLabel->concert = array('concert', 'een concert');
		$pLabel->theaterconcert = array('concert', 'een concert');
		$pLabel->opera = array('opera', 'opera');
		$pLabel->muziek = array('concert', 'een concert');
		$pLabel->klassiek = array('concert', 'een concert');
		
		$genre = $this->genre[0];
	
		if ($pLabel->$genre != null) {
			$tLabel = $pLabel->$genre;
		} else {
			$tLabel = array('voorstelling', 'een voorstelling');
		}
		
		//If performance month is equal to May, don't use a dot in the heading.
		if (strpos($this->date->AdDate, 'mei') !== false) {
			$hDate = substr($this->date->AdDate, 0, -1);
		} else {
			$hDate = $this->date->AdDate;
		}
		
		if ($type == 'title') {
			
			//Templates Title ad
			
			//Determine if the character length of the artist value is higher than 30 characters
			//Define header artist (hArtist) variable
			if (strlen($artist) > 30) {
				//If artist value contains an ampersand (&), split it into parts. Use the part that has most characters as hArtist value
				if (strpos($artist, '&') !== false) {
					$splittedArtist = explode('&', $artist);
					//Sort artist value by length in ascending order
					usort($splittedArtist, array($this, 'sortByLength'));
					//Loop artist parts
					foreach($splittedArtist as $tempSplit) {
						//Select part that has most characters, but less than or equal to 30 as hArtist valuee
						if (strlen($tempSplit) <= 30) {
							$hArtist = trim($tempSplit);
						}
					}
					
				}
				//If artist value contains a colon (:), only use the value after the colon
				if (strpos($artist, ':') !== false) {
					$hArtist = substr($artist, strpos($artist, ':')+1);
				}

			} else {
				$hArtist = $artist;
			}
			
			//Check if titles length is more than 30 characters. Depend heading values on that
			if (strlen($title) > 30) {
				//Divide heading1 and heading2 in 2 elements
				$heading[0] = array($hArtist, $hDate.' in '.$this->location);
				if (strlen(ucfirst($genre).' - '.$hArtist) > 30) {
					$heading[1] = array($hArtist, $this->venue);
					$heading[2] = array($hArtist, $this->date->AdDateFull.' in '.$this->location);
				} else {
					$heading[1] = array(ucfirst($genre).' - '.$hArtist, $this->venue);
					$heading[2] = array($hArtist.' - '.ucfirst($genre), $this->venue);
				}
			} else {
				//Divide heading1 and heading2 in 2 elements
				$heading[0] = array($title, $hDate.' in '.$this->location);
				//Determine if artist heading value is too long or doesn't exist
				if (strlen($hArtist) > 30 || strlen($hArtist) === 0) {
					$heading[1] = array($title, $this->date->AdDateFull.' in '.$this->location);
				} else {
					$heading[1] = array($title, $hArtist);
				}
				$heading[2] = array($title, $this->venue);
			}
			
			//Check if heading 2 is still empty
			/*if ($performance == null) {
				$heading[1] = array($title, $hDate.' - '.ucfirst($this->genre).' in '.$this->location);
			}
			*/
			
			//Sort description length from short to long. Needed to iterate them to fit the 80 characters.
			$template[0] = array(
				"Naar ".$tLabel[1]." in ".$this->location."? Koop kaarten voor ".$title.".",
				"Naar ".$tLabel[1]." in ".$this->location."? Bestel nu je kaarten voor ".$title.".",
			);
			$template[1] = array(
				"Kom naar ".$title." in ".$this->location.". Koop tickets voor ".$this->date->AdDate,
				"Kom naar ".$title." in ".$this->venue.". Koop tickets voor ".$this->date->AdDate,
				"Kom naar ".$title." in ".$this->venue.". Bestel nu je tickets voor ".$this->date->AdDate,
			);
			$template[2] = array(
				"Geniet van ".$title.". Koop nu tickets.",
			    "Geniet van ".$title.". Koop nu tickets voor ".$this->date->AdDate,
				"Geniet van ".$title." met ".$artist.". Koop nu tickets voor ".$this->date->AdDate,
				"Geniet van ".$title." door ".$artist.". Koop tickets voor ".$this->date->AdDate,
				"Geniet van ".$title." door ".$artist.". Koop nu je tickets voor ".$this->date->AdDate,
			);
			
		} 
		if ($type == 'artist' || $type == 'multiple-artists') {
			//If performance title exist
			if (strlen($this->title)>1) {
				$performance = trim($this->title);
			} else {
				$performance = $this->trimArtist($this->subtitle)[0];
			}
			
			if ($type == 'multiple-artists') {
				$title = '{KeyWord:'.$this->performers[0].'}';
			}
			
			//Templates Artist ad
			//Check if title will be longer than 30 characters
			//-10 characers for keyword insertion
			//+6 characters for additional string 'Naar ?'
			if (strlen($title)+6 > 30 || ($type == 'multiple-artists' && strlen($title)-4 > 30)) {
				$heading[0] = array($title, $hDate.' in '.$this->location);
			} else {
				$heading[0] = array('Naar '.$title.'?', $hDate.' in '.$this->location);
			}
			
			//If no title available
			if (trim($this->title) == null) {
				$heading[1] = array($title, $hDate.' - '.ucfirst($genre).' in '.$this->location);
			} else {
				if (strlen($performance) > 30) {
					$heading[1] = array($title, $this->date->AdDateFull.' in '.$this->location);
				} else {
					$heading[1] = array($title, $performance);
				}
			}
			$heading[2] = array($title, $this->venue);
			
			
			$template[0] = array(
				"Naar ".$title."? Koop nu je kaarten.",
				"Naar ".$title."? Koop kaarten voor ".$tLabel[1]." in ".$this->location.".",
				"Naar ".$title."? Koop kaarten voor ".$performance.".",
				"Naar ".$title."? Koop nu kaarten voor ".$performance.".",
				"Naar ".$title."? Bestel nu je kaarten voor ".$tLabel[1]." in ".$this->location.".",
				
			);
			$template[1] = array(
				"Kom naar ".$title." in ".$this->location.". Koop tickets voor ".$this->date->AdDate,
				"Kom naar ".$title." in ".$this->venue.". Koop tickets voor ".$this->date->AdDate,
				"Kom naar ".$title." in ".$this->venue.". Bestel nu je tickets voor ".$this->date->AdDate,
			);
			$template[2] = array(
				"Geniet van ".$title.". Koop nu je tickets",
			    "Geniet van ".$title.". Koop nu tickets voor ".$this->date->AdDate,
			    "Geniet van ".$title." in ".$performance.". Koop nu tickets.",
				"Geniet van ".$title." in ".$performance.". Koop tickets voor ".$this->date->AdDate,
				"Geniet van ".$title." in ".$performance.". Koop nu tickets voor ".$this->date->AdDate,
			);
		}
			
			
			foreach($template as $key => $tpl) {
				$ad[$key]->heading[0] = $heading[$key][0];
				$ad[$key]->heading[1] = $heading[$key][1];
				
				foreach ($template[$key] as $description) {
					//Check if description length <= 80 characters
					if (strlen($description) <= 80 || ($type == 'multiple-artists' && strlen($description) <= 90)) {
						$ad[$key]->description = $description;
					}
				}
				
				//If the description length is still empty because > 80 characters, use the first (shortest) description
				if ($ad[$key]->description == '') {
					$ad[$key]->description = $template[$key][0];
					//If third ad
					if ($key == 2) {
						$ad[$key]->heading[0] = $heading[0][0];
						$ad[$key]->heading[1] = $heading[0][1];
						$ad[$key]->description = $template[$key][1];
					}
				}
				
				if ($type == 'multiple-artists') {
					if (strlen($this->title)>1) {
						$pathTitle = $this->title;
					} else {
						$pathTitle = $this->subtitle;
					}
					$pathString = strtolower(trim(preg_replace('/( de | een | en | het )/', ' ', trim($pathTitle))));
				} else {
					$pathString = strtolower(trim(preg_replace('/( de | een | en | het )/', ' ', $title)));
				}
				
				//Check if pathString length <= 15 characters
				
				if (strlen($pathString) <= 15) {
					$ad[$key]->path[0] = strtolower($genre);
					$ad[$key]->path[1] = str_replace('--', '-', str_replace(' ', '-', $pathString));
				}
				
				//Check if pathstring has more than 15 characters and split the words to the path fields
				else if (strlen($pathString) > 15) {
					//Check if keyword insertion has been applied. If keyword doesn't fit, choose performance name for display url fields
						$ad[$key]->path[0] = str_replace('--', '-', str_replace(' ', '-', trim($this->truncate($pathString, 15))));
						$ad[$key]->path[1] = str_replace('--', '-', str_replace(' ', '-', trim($this->truncate(substr($pathString, strlen($this->truncate($pathString, 15)), 30), 15))));
				}
			}
			
		
		return $ad;
	}
	
	private function createKeywords($name, $type) {
			$placements = new stdClass();
			$placements->toneel = array('voorstelling', 'toneel', 'tickets', 'theater');
			$placements->cabaret = array('cabaret', 'theater', 'tickets');
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
			$genre = $this->genre[0];
		
			$keywordList = array();
			
			//Check if keyword insertion has been applied
			if ($type == 'multiple-artists') {
				$keywordList = $name;
			} else {
				array_push($keywordList, strtolower($name));

				//Combine performer and performance name as keyword
				if ($type == 'title') {
					array_push($keywordList, strtolower($name).' '.strtolower($this->trimArtist($this->subtitle)));
				}
				//Combine venue or location and performance name as keyword
				if (strlen($name) > 15) {
					array_push($keywordList, strtolower($name).' '.strtolower($this->location));
				} else {
					array_push($keywordList, strtolower($name).' '.strtolower($this->location));
					array_push($keywordList, strtolower($name).' '.strtolower($this->venue));
				}
			}

		
			//Loop keyword list
			foreach($placements as $keyWord => $keyValue) {
				//Check if genre has keyword array
				if (stripos($genre, $keyWord) !== false) {
						foreach($keyValue as $tempKey => $tempVal) {
							//Add keywords
								if (stripos($name, $tempVal) === false) {
									array_push($keywordList, strtolower($name).' '.strtolower($tempVal));
								}
							
							}
							
				}
	
			}
		//Remove single words from keywords list
		foreach ($keywordList as $key => $word) {
			if (strpos($word, ' ') === false) {
				unset($keywordList[$key]);
			}
		}
		return $keywordList;
	}
}


?>