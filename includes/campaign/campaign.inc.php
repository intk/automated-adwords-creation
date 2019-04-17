<?php
#error_reporting(E_ALL);
#ini_set('display_errors', 1);
include('includes/methods/keywords/keywords.inc.php');

//Contains name, date, target location, sitelink extentions, AdGroups, Ads, keywords
class Campaign {
	private $template;
	private $price;
	private $title;
	private $subtitle;
	private $venue;
	private $city;
	private $performers;
	private $link;
    public function __construct($production, $template) {

		//Parse campaign info
		$itemDate = $production->date->time;
		$this->template = $template;
		$this->title = $this->trimStr($production->title);
		$this->subtitle = $this->trimStr($production->subtitle);
		// Determine if subtitle exists or not. Depend the campaign name on it
		if (strlen(trim($this->subtitle)) > 1) {
			$subtitlePlacement = ' - '.$production->subtitle;
		} else {
			$subtitlePlacement = '';
		}
		//Campaign name format [[YYYY-MM-DD]] [performance] - [performer]
		$this->name = trim('['.$this->formatDate($itemDate)[3].'] '.$this->title.$subtitlePlacement);
		if (strlen($this->name) > 120) {
			$this->name = substr($this->name, 0, strpos($this->name, ' - '));
		}
		$this->venue = $production->venue;
		$this->city = $production->location;
		$this->genre = $production->genre;
		$this->performers = $production->performers;
		$this->date->time = $itemDate;
		$this->date->GaDate = $this->formatDate($itemDate)[0];	
		$this->date->AdDate = $this->formatDate($itemDate)[1];
		$this->date->AdDateFull = $this->formatDate($itemDate)[2];
		$this->link = $production->link;
		//$this->date->dateString = $production->date->dateString;
    }
	
	private function trimStr($string) {
		//Replacements for unnecessary abbreviations
		$replace = array('e.a.', 'e.a', 'e.v.a.', '|', '/', ' + ');
		$replacement = array('', '', '', ' - ', ' - ', ' - ');
		
		// Determine if string is written in capitals
		if (strtoupper($string) == $string) {
			//Uppercase first character of each word in the string
			$string = ucwords(strtolower($string));
		}

		//Remove number and plus sign from string, only when plus sign is present
		if (preg_match("/\d\+/", $string, $match)) {
			$output = str_replace('+', '', preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'‘’&,-.:]+/u", " ", str_replace($replace, $replacement, $string)))));
		} else {
			$output = str_replace('+', '', preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'‘’&,-.:[0-9]]+/u", " ", str_replace($replace, $replacement, $string)))));
		}
		
		return $output;
	}
	
	private function trimHead($string) {
		// Remove invalid characters from heading string
		if (strpos($string, '’') !== false && strpos($string, '‘') === false) {
			$string = str_replace("’", "'", $string);
		} 
		if (preg_match("/\d\+/", $string, $match)) {
			$output = ucfirst(preg_replace('/[\[{\(\].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()&-.:]+/u", " ", $string))));
		} else {
			$output = ucfirst(preg_replace('/[\[{\(\].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()&-.:[0-9]]+/u", " ", $string))));
		}
		return $output;
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
	
	private function sortByDescLength($a,$b) {
    	return strlen($b)-strlen($a);
	}
	
	//Split performers from string
	private function getPerformers($string) {
		$replace = array('e.a.', 'e.v.a.', 'o.a.');
		$replacement = array('', '', '');
		
		$performerString = str_replace($replace, $replacement, trim($string));
		$performers = preg_split('/(,| en | i.s.m. | ism |met | - | -- | + )+/i', $performerString);
		// If combined performers have a character length of more than 30
		if (strlen($performerString) > 30) {
			$performers = preg_split('/(,| en | i.s.m. | ism |met | - | -- | + | & )+/i', $performerString);
		}
		$performers = array_map('trim', array_unique($performers, SORT_STRING));
		foreach($performers as $key => $performer) {
			//Check if misplaced dash (-) occurs in string
			if (strpos($performer, '-') == (strlen($performer) -1)) {
				unset($performers[$key]);
			}
		}
		usort($performers,array($this, 'sortByDescLength'));
		return $performers;
	}
		
	public function createAdgroup() {
		$tempHeadings = array();
		$adGroups = array();

		$placements = new stdClass();
		$placements->toneel = array('toneel', 'tickets', 'theater');
		$placements->cabaret = array('cabaret', 'theater', 'tickets');
		$placements->musical = array('theater');
		$placements->college = array('college', 'theater');
		$placements->familie = array("familie", "theater");
		$placements->jeugd = array("familie", "theater");
		$placements->dans = array('dans', 'theater');
		$placements->serie = array('theater');
	    $placements->concert = array('concert', 'muziek', 'live');
	    $placements->expo = array('expo', 'tentoonstelling');
		$placements->show = array('concert', 'theater');
		$placements->muziek = array('concert');
		$placements->festival = array('concert', 'muziek', 'festival', 'live');
		$placements->klassiek = array('concert', 'theater');
		$placements->overig = array('theater');
		$placements->kunst = array('collectie', 'kunst');
		$placements->opera = array('opera', 'theater');
		$placements->specials = array('theater');
		$placements->circus = array('circus', 'theater');
		$placements->entertainment = array('theater');
		$placements->film = array('film');
		if (count($this->genre) > 1) {
			$genre = $this->genre[1];
		} else {
			$genre = $this->genre[0];
		}

		$placementsList = array();

		//Loop keyword list
		foreach($placements as $keyWord => $keyValue) {
			//Check if genre has keyword array
			if (stripos($genre, $keyWord) !== false) {
				$placementsList = $keyValue;
			}
		}

		$keywordsObj = new stdClass();
		$keywordsObj->adgroup = array();
		$manyPerformers = false;

		// Create keywords and adgroup of title
		if (strlen($this->title) > 1) {
			$titleObj = new Keywords($this->title, $this->venue[0], $this->city, $placementsList, 'title');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);
		}

		if (count($this->performers) > 0 && count($this->performers) <= 11 && $manyPerformers == false) {
			foreach ($this->performers as $performer) {
				$performersObj = new Keywords($performer, $this->venue[0], $this->city, $placementsList, 'performer');
				$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $performersObj->adgroup);
			}
			$manyPerformers = true;
		}

		// Create keywords and adgroups of title + genre
		if (count($keywordsObj->adgroup) < 2 && count($this->performers) < 1) {
			$addObj = new Keywords($this->title.' '.$this->genre[0], $this->venue[0], $this->city, $placementsList, 'title');
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $addObj->adgroup);
		}

		foreach ($keywordsObj->adgroup as $key => $adgroup) {

			$this->adgroup[$key]->name = $adgroup['name'];
			$this->adgroup[$key]->type = $adgroup['type'];

			// Determine ad group type
			if ($adgroup['name'] == $this->title) {
				$this->adgroup[$key]->type = 'title';
			}

			if (strlen(trim($this->subtitle)) > 1) {
				$subtitle = $this->trimArtist($this->subtitle);
			} 
			else if (strlen(trim($this->subtitle)) < 1 && count($this->performers) > 0) {
				$subtitle = $this->performers[0];
			}

			$this->adgroup[$key]->ad = $this->createAds(trim($this->adgroup[$key]->name), $this->adgroup[$key]->type, $subtitle);
			$this->adgroup[$key]->keywords = $adgroup['keywords'];
		}

		// Create keywords and adgroups of performers list
		if (count($this->performers) > 10 && $manyPerformers == false) {
			$adGroupCount = count($this->adgroup);
			$this->adgroup[$adGroupCount]->name = 'Performers';
			$this->adgroup[$adGroupCount]->type = 'performer';
			$this->adgroup[$adGroupCount]->ad = $this->createAds(trim($this->adgroup[$adGroupCount]->name), $this->adgroup[$adGroupCount]->type, $this->trimArtist($this->subtitle));
			$this->adgroup[$adGroupCount]->keywords = $this->performers;
		}

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
			$tempArtist =  preg_split('/(, | i.s.m. | ism | ft. | feat. | -- |met )+/i', $artist);
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
	
	//Remove unwanted characters from string
	private function removeChars($string) {
		return trim(str_replace(array("/", ":", ";", ".", ","), array("", "", "", "", ""), $string));
	}

	# Replace variables in template text
	private function replaceTpl($property, $replace, $replacement, $Maxcharacters) {

		$tempString = str_replace($replace, $replacement, $property); 
		#$tempString = $property;

		/*
		# Limit string to characters
		for ($i = 0; $i < count($replace); $i++) {
			if (strpos($property, $replace[$i]) > 1 && strlen($tempString) > $characters) {
				# Split replacement by spaces
				$replacementSplitted = explode(" ", $replacement[$i]);

				# Iterate splitted elements and remove element by character limit
				foreach ($replacementSplitted  as $splitted) {

				}
			}
		}
		*/

		return $tempString;
	}
	
	private function createAds($title, $type, $performer) {
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
		$pLabel->film = array('film', 'de film', 'een film');
		$pLabel->concert = array('concert', 'een concert');
		$placements->expo = array('expo', 'de expo');
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

		# If performer ad group, change swap title and performer attribute
		if ($type == 'performer') {
			$performer = $title;
			$title = $this->title;
		}


		# Template replacements
		$replace = array('[performer]', '[title]', '[genre]', '[venue]', '[location]', '[date]', '[dateFull]');

		# Add keyword insertion when performers > 10
		if (count($this->performers) > 10 && $type == 'performer') {
			$title = '{KeyWord:'.$this->performers[0].'}';
		}

		if ($type == 'title') {
			$replacement = array($performer, $title, $tLabel[1], $this->venue[0], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		} else {
			$replacement = array($title, $this->title, $tLabel[1], $this->venue[0], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		}

		# Decode ads template in JSON format and iterate
		foreach (json_decode($this->template) as $template) {
			# Choose template that has the same type as the type argument
			if ($template->type == $type) {

				# Iterate ads template
				foreach($template->ads as $adkey => $adProperties) {
					$ad[$adkey]->heading[0] = $this->replaceTpl($adProperties->heading1, $replace, $replacement, 30);
					$ad[$adkey]->heading[1] = $this->replaceTpl($adProperties->heading2, $replace, $replacement, 30);
					$ad[$adkey]->heading[2] = $this->replaceTpl($adProperties->heading3, $replace, $replacement, 30);
				}

			}

		}


		/*

			//Sort description length from short to long. Needed to iterate them to fit the 90 characters.
			// Loop adgroup
			foreach($this->template[$type] as $groupkey => $adgroups) {
				foreach ($adgroups as $adkey => $value) {
					
					// Replace variables in ad templates
					$replace = array('[artist]', '[title]', '[genre]', '[venue]', '[venueShort]', '[location]', '[date]', '[dateFull]');
					$replacement = array($hArtist, $title, $tLabel[1], $venue[0], $venue[1], $this->city, $this->date->AdDate, $this->date->AdDateFull);
					$template[$groupkey][$adkey] = str_replace($replace, $replacement, $value); 
				}
				
			}

			/*
		
					//Check if description length <= 90 characters
					if (strlen($description) <= 90 || ($type == 'multiple-artists' && strlen($description) <= 90)) {
						$ad[$key]->description = $description;
					}
				}
				
				//If the description length is still empty because > 90 characters, use the first (shortest) description
				if ($ad[$key]->description == '') {
					$ad[$key]->description = $template[$key][0];
					//If third ad
					if ($key == 2) {
						$ad[$key]->heading[0] = $heading[0][0];
						$ad[$key]->heading[1] = $heading[0][1];
						$ad[$key]->description = $template[$key][1];
					}
				}

				// Determine if both heading 1 and heading 2 have the same value
				if ($ad[$key]->heading[0] == $ad[$key]->heading[1]) {
					$ad[$key]->heading[1] = $this->template['placeholder'];
				}

				
				if ($type == 'multiple-artists') {
					if (strlen($this->title)>1) {
						$pathTitle = $this->title;
					} else {
						$pathTitle = $this->subtitle;
					}
					$pathString = strtolower(trim(preg_replace('/( de | een | en | het |:|,)/', ' ', trim($pathTitle))));
				} else {
					$pathString = strtolower(trim(preg_replace('/( de | een | en | het |:|,)/', ' ', $title)));
				}
				
				//Check if pathString length <= 15 characters
				
				if (strlen($pathString) <= 15) {
					if (strpos($genre, '_') > 1)  {
						$genre = explode('_', $genre)[0];
					}
					$ad[$key]->path[0] = $this->removeChars(strtolower($genre));
					$ad[$key]->path[1] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathString)));
				}
				
				//Check if pathstring has more than 15 characters and split the words to the path fields
				else if (strlen($pathString) > 15) {
					//Check if keyword insertion has been applied. If keyword doesn't fit, choose performance name for display url fields
						$ad[$key]->path[0] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', trim($this->truncate($pathString, 15)))));
						$ad[$key]->path[1] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', trim($this->truncate(substr($pathString, strlen($this->truncate($pathString, 15)), 30), 15)))));

					//If both paths still have more than 15 characters, split them by their inner parts
					if (strlen($ad[$key]->path[0]) > 15) {
						$wordSplit = json_decode(file_get_contents("https://picturage.nl/intk/theaterads/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$key]->path[0]));
						if (strlen($wordSplit[0]) <= 15 && strlen($wordSplit[0]) > 1) {
							$ad[$key]->path[0] = $wordSplit[0];
							$ad[$key]->path[1] = $wordSplit[1];
						}
					}
					if (strlen($ad[$key]->path[1]) > 15) {
						$wordSplit = json_decode(file_get_contents("https://picturage.nl/intk/theaterads/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$key]->path[1]));
						if (strlen($wordSplit[0]) <= 15 && strlen($wordSplit[0]) > 1) {
							$ad[$key]->path[0] = $wordSplit[0];
							$ad[$key]->path[1] = $wordSplit[1];
						}
					}
				}
			}*/
			
		
		return $ad;
	}
	
	public function toDatabase($theaterId) {
		return "(theaterId, title, subtitle, genre, performanceDate, creationDate, link) VALUES (".$theaterId.", '".$this->title."', '".$this->subtitle."', '".implode(';', $this->genre)."', '".$this->date->time."', '".time()."', '".$this->link."')";
	}
}


?>