<?php

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
		$this->price = $production->price;
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
		$monthFR = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre");
		$monthFRFull =  array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre");
		$monthEN = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
		$day = date('d', $time);
		if ($day < 10) { $day = substr($day, 1); }
		$output[0] = date('M d, Y', $time);
		$output[1] = $day.' '.str_ireplace($monthEN, $monthFR, date('M', $time)).'';
		$output[2] = $day.' '.str_ireplace($monthEN, $monthFRFull, date('M', $time));
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
	    $placements->concert = array('concert', 'musique', 'live');
	    $placements->expo = array('expo');
		$placements->show = array('concert', 'theater');
		$placements->muziek = array('concert');
		$placements->festival = array('concert', 'musique', 'festival', 'live');
		$placements->klassiek = array('concert', 'theater');
		$placements->overig = array('theater');
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

		// Create keywords and adgroups of it
		if (strlen($this->title) > 1) {
			$titleObj = new Keywords($this->title, $this->venue[0], $this->city, $placementsList);
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);
		}

		if (strlen($this->subtitle) > 1) {
			$subtitleObj = new Keywords($this->subtitle, $this->venue[0], $this->city, $placementsList);
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $subtitleObj->adgroup);
		}


		foreach ($keywordsObj->adgroup as $key => $adgroup) {
			$this->adgroup[$key]->name = $adgroup['name'];
			$this->adgroup[$key]->type = $adgroup['type'];
			$this->adgroup[$key]->ad = $this->createAds(trim($this->adgroup[$key]->name), $this->adgroup[$key]->type, $this->trimArtist($this->subtitle));
			$this->adgroup[$key]->keywords = $adgroup['keywords'];
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
		$pLabel->cinema = array('film', 'le film', 'un film');
		$pLabel->concert = array('concert', 'un concert');
		$pLabel->theaterconcert = array('concert', 'un concert');
		$pLabel->expo = array('expo', 'le expo');
		$pLabel->opera = array('opera', 'opera');
		$pLabel->muziek = array('concert', 'un concert');
		$pLabel->klassiek = array('concert', 'un concert');
		
		$genre = $this->genre[0];
	
		if ($pLabel->$genre != null) {
			$tLabel = $pLabel->$genre;
		} else {
			$tLabel = array('performance', 'la performance');
		}
		
		//If performance month is equal to May, don't use a dot in the heading.
		if (strpos($this->date->AdDate, 'mei') !== false) {
			$hDate = substr($this->date->AdDate, 0, -1);
		} else {
			$hDate = $this->date->AdDate;
		}
		
		//Set location values for the headings
		if (strlen($this->date->AdDateFull.' '.$this->template['prep'].' '.$this->venue[0]) > 30) {
				if (count($this->venue) > 1 && strlen($this->date->AdDateFull.' '.$this->template['prep'].' '.$this->venue[1]) <= 30) {
					$hLocation[0] = $this->venue[1];
				} else {
					$hLocation[0] = $this->city;
				}
			} else {
				$hLocation[0] = $this->venue[0];
			}
			
			$hLocation[1] = $this->venue[0];
			
			
			if (strlen($hDate.' '.$this->template['prep'].' '.$this->venue[0]) > 30) {
				if (count($this->venue) > 1 && strlen($hDate.' '.$this->template['prep'].' '.$this->venue[1]) <= 30) {
					$hLocation[1] = $this->venue[1];
				} else {
					$hLocation[1] = $this->city;
				}
			} else {
				$hLocation[1] = $this->venue[0];
			}
			
			$hLocation[2] = $this->venue[0];
			
			if (count($this->venue) > 1) {
				$venue[0] = $this->venue[0];
				$venue[1] = $this->venue[1];
			} else {
				$venue[0] = $this->venue[0];
				$venue[1] = $this->city;
			}
		
			if ($this->genre[0] == 'film') {
				$hGenre = ' - '.ucfirst($this->genre[0]);
			} else {
				$hGenre = '';
			}

		if ($this->city == $hLocation[0]) {
			$locPrep = 'in';
		} else {
			$locPrep = $this->template['prep'];
		}
		
		if ($type == 'title') {
			
			//Templates Title ad
			
			//Determine if the character lenghth of the title is higher than 30 characters and an artist doesn't exist
			if (strlen($artist) <= 1 || strlen($title)>30) {
				$artist = $title;
			}
			
			//Determine if the character length of the artist value is higher than 30 characters
			//Define header artist (hArtist) variable
			if (strlen($artist) > 30) {
				//If artist value contains an ampersand (&), split it into parts. Use the part that has most characters as hArtist value
				if (strpos($artist, '&') !== false || strpos($artist, 'feat.') !== false || strpos($artist, ' - ') !== false) {
					$splittedArtist = preg_split('/(&|feat.| - )/', $artist);
					//Sort artist value by length in ascending order
					usort($splittedArtist, array($this, 'sortByLength'));
					//Loop artist parts
					foreach($splittedArtist as $tempSplit) {
						//Select part that has most characters, but less than or equal to 30 as hArtist valuee
						if (strlen($tempSplit) <= 30) {
							$hArtist = trim($tempSplit);
						}
					}
					
				} else {
					$hArtist = $artist;
				}
				
				//If artist value contains a colon (:), only use the value after the colon
				if (strpos($artist, ':') !== false) {
					$hArtist = substr($artist, strpos($artist, ':')+1);
				}

			} else {
				$hArtist = $artist;
			}
			
			//If title value contains a colon (:) and is longer than 30 characters, only use the value after the colon
			if (strpos($title, ':') !== false && strlen($title) > 30) {
				$title = trim(substr($title, strpos($title, ':')+1));
			}
			
			if (strlen($hDate.' '.$this->template['prep'].' '.$this->venue[0]) > 30) {
				$hDateString = $hDate.' '.$this->venue[0];
				if (strlen($hDateString) > 30) {
					$hDateString = $hDate.' '.$this->template['prep'].' '.$venue[1];
				}
			} else {
				$hDateString = $hDate.' '.$this->template['prep'].' '.$this->venue[0];
			}
			
			
			//Check if titles length is more than 30 characters. Depend heading values on that
			if (strlen($title) > 30) {
				//Divide heading1 and heading2 in 2 elements
				
				$heading[0] = array($this->trimHead($hArtist), $this->date->AdDateFull.' '.$locPrep.' '.$hLocation[0]);
				if (strlen(ucfirst($genre).' - '.$this->trimHead($hArtist)) > 30) {
					$heading[1] = array($this->trimHead($hArtist), $hDateString);
					$heading[2] = array($this->trimHead($hArtist), ucfirst($hLocation[2]));
				} else {
					$heading[1] = array(ucfirst($genre).' - '.$this->trimHead($hArtist), ucfirst($hLocation[1]));
					$heading[2] = array($this->trimHead($hArtist).' - '.ucfirst($genre), ucfirst($hLocation[2]));
				}
			} else {
				//Divide heading1 and heading2 in 2 elements
				$heading[0] = array($this->trimHead($title).$hGenre, $this->date->AdDateFull.' '.$locPrep.' '.$hLocation[0]);
				//Determine if artist heading value is too long or doesn't exist
				if (strlen($this->trimHead($hArtist)) > 30 || strlen($this->trimHead($hArtist)) === 0) {
					$heading[1] = array($this->trimHead($title).$hGenre, $hDateString);
				} else {
					// Determine if artist has same value as title
					if ($title == $hArtist) {
						$h2Val = $hLocation[2].' '.$this->city;
					} else {
						$h2Val = $hArtist;
					}
					$heading[1] = array($this->trimHead($title).$hGenre, $this->trimHead($h2Val));
				}
				$heading[2] = array($this->trimHead($title).$hGenre, ucfirst($hLocation[2]));
			}


			// Determine if display date boolean is set
			if (strpos($this->template['displayDate'], 'No') !== false) {
				$heading[0][1] = $this->template['placeholder'];
			}

			//Check if heading 2 is still empty
			/*if ($performance == null) {
				$heading[1] = array($title, $hDate.' - '.ucfirst($this->genre).' in '.$this->city);
			}
			*/
			
		
			// Assign movietemplate for third ad
			if ($this->genre[0] === 'film') {
				$this->template[$type][2] = $this->template['movieTitle'][0];
			}

			// Assign template for free events
			else if (strpos($this->price, 'free') !== false) {
				$this->template[$type] = $this->template['freeTitle'];
			}

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
				$heading[0] = array($title, $hDate.' '.$locPrep.' '.$hLocation[0]);
			} else {
				$heading[0] = array('Voir '.$title.'?', $hDate.' '.$locPrep.' '.$hLocation[0]);
			}
			
			//If no title available
			if (trim($this->title) == null) {
				$heading[1] = array($title, $hDate.' - '.ucfirst($genre).' '.$this->template['prep'].' '.$this->venue[0]);
			} else {
				if (strlen($performance) > 30) {
					if (strlen($this->date->AdDateFull.' '.$this->template['prep'].' '.$this->venue[0]) > 30) {
						$heading[1] = array($title, $this->date->AdDate.' '.$this->template['prep'].' '.$venue[1]);
					} else {
						$heading[1] = array($title, $this->date->AdDateFull.' '.$this->template['prep'].' '.$venue[0]);
					}
				} else {
					$heading[1] = array($title, date('d.m.y', $this->date->time).' '.$this->template['prep'].' '.$venue[0]);
				}
			}
			$heading[2] = array($title, ucfirst($hLocation[2]));

			if ($type == 'multiple-artists') {
				$heading[0][0] = "Fan de ".$title."?";
				$heading[1][0] = "Fan de ".$title."?";
				$heading[2][0] = $title."?";
			}


			// Assign template for free events
			if (strpos($this->price, 'free') !== false) {
				$this->template[$type] = $this->template['freeArtist'];
			} else {
				$this->template[$type] = $this->template['artist'];
			}

			// Assign movie template for artist
			if ($this->genre[0] === 'film') {
				$this->template[$type] = $this->template['movieArtist'];
			}

			// Assign template for SoundsLike
			if ($type == 'multiple-artists') {
				$this->template[$type] = $this->template['SoundsLike'];
			}

			
			//Sort description length from short to long. Needed to iterate them to fit the 90 characters.
			// Loop adgroups
			foreach($this->template[$type] as $groupkey => $adgroups) {
				foreach ($adgroups as $adkey => $value) {

					// Replace variables in ad templates
					$replace = array('[artist]', '[performance]', '[genre]', '[venue]', '[venueShort]', '[location]', '[date]', '[dateFull]', '[movie]');
					$replacement = array($title, $performance, $tLabel[1], $venue[0], $venue[1], $this->city, $this->date->AdDate, $this->date->AdDateFull, $tLabel[2]);
					$template[$groupkey][$adkey] = str_replace($replace, $replacement, $value); 
				}

			}
			
			if ($this->genre[0] === 'film') {
				//Custom template for movies
				
				//Sort description length from short to long. Needed to iterate them to fit the 90 characters.
				// Loop adgroups
				foreach($this->template['movieArtist'] as $groupkey => $adgroups) {
					foreach ($adgroups as $adkey => $value) {

						// Replace variables in ad templates
						$replace = array('[artist]', '[performance]', '[genre]', '[venue]', '[venueShort]', '[location]', '[date]', '[movie]');
						$replacement = array($title, $performance, $tLabel[1], $venue[0], $venue[1], $this->city, $this->date->AdDate, $tLabel[2]);
						$template[$groupkey][$adkey] = str_replace($replace, $replacement, $value); 
					}

				}
			}



			// Determine if display date boolean is set
			if ($this->template['displayDate'] == "No") {
				if (strlen($performance.' - '.$this->template['placeholder']) <= 30) {
					$heading[$key][1] = $performance.' - '.$this->template['placeholder'];
				} else {
					$heading[$key][1] = $this->template['placeholder'];
				}
			}
		}
			
			
			foreach($template as $key => $tpl) {
				$ad[$key]->heading[0] = $heading[$key][0];
				$ad[$key]->heading[1] = $heading[$key][1];
				
				foreach ($template[$key] as $description) {
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
			}
			
		
		return $ad;
	}
	
	public function toDatabase($theaterId) {
		return "(theaterId, title, subtitle, genre, performanceDate, creationDate, link) VALUES (".$theaterId.", '".$this->title."', '".$this->subtitle."', '".implode(';', $this->genre)."', '".$this->date->time."', '".time()."', '".$this->link."')";
	}
}


?>