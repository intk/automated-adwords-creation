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
	private $maxPerformers;
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
			$this->name = trim(substr($this->name, 0, strpos($this->name, ' - ')));
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
		$this->maxPerformers = 10;
		//$this->date->dateString = $production->date->dateString;
    }
	
	private function trimStr($string) {
		//Replacements for unnecessary abbreviations and characters
		$replace = array('e.a.', 'e.a', 'e.v.a.', '|', '/', ' + ', '?', '!', '...', '..');
		$replacement = array('', '', '', ' - ', ' - ', ' - ', '', '', '', '');
		
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
		$performersList = false;

		// If this is true, don't make another ad group from the title
		if (count($this->performers) > 0 && $this->performers !== false) {
			$performersList = true;
		}

		// Create keywords and adgroup of title
		if (strlen($this->title) > 1) {

			// Add performer name to keywords placementList of title ad group
			$titlePlacementList = $placementsList;
			if (strlen(trim($this->subtitle)) > 1) {
				foreach($this->getPerformers(trim($this->subtitle)) as $string) {
					array_push($titlePlacementList, $string);
				}
			}

			if (count($this->performers) > 1) {
				array_push($titlePlacementList, $this->performers[0]);
			}

			$titleObj = new Keywords($this->title, $this->venue[0], $this->city, $titlePlacementList, 'title');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);
		}

		// Create keywords and adgroup of subtitle
		if (strlen($this->subtitle) > 1) {
			$subtitleObj = new Keywords($this->subtitle, $this->venue[0], $this->city, $placementsList, 'performer');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $subtitleObj->adgroup);
		}

		if (count($this->performers) > 0 && $this->performers !== false && count($this->performers) <= $this->maxPerformers && $manyPerformers == false) {
			foreach ($this->performers as $performer) {
				$performersObj = new Keywords($performer, $this->venue[0], $this->city, $placementsList, 'performer');
				$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $performersObj->adgroup);
			}
			$manyPerformers = true;
		}


		// Create keywords and adgroups of title + genre if < 2 adgroups created
		if (count($keywordsObj->adgroup) < 2 && $performersList == false) {

			/*
			// Insert placement text
			$newElement = $this->title.' '.$this->genre[0];
			*/

			// Remove word that contains placement text
			$splittedPair = explode(' ', $this->title);
			$found = array_filter($splittedPair, function($value) {return strpos($value,  $this->genre[0]) !== false;});
			if ($found !== false) {
				unset($splittedPair[key($found)]);
			}

			// Add genre name to filtered element
			$newElement = implode(' ', $splittedPair).' '. $this->genre[0];


			$titleObj = new Keywords($newElement, $this->venue[0], $this->city, $placementsList, 'title');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);
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

			// If subtitle is empty use first performer as subtitle
			else if (strlen(trim($this->subtitle)) < 1 && count($this->performers) > 0) {
				$subtitle = $this->performers[0];
			}

			$this->adgroup[$key]->ad = $this->createAds(trim($this->adgroup[$key]->name), $this->adgroup[$key]->type, $subtitle);
			$this->adgroup[$key]->keywords = $adgroup['keywords'];
		}

		// Create keywords and adgroups of performers list
		if (count($this->performers) > $this->maxPerformers && $manyPerformers == false) {
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

		# Replace string with replacements and bring first character to capital
		$tempString = ucfirst(str_replace($replace, $replacement, $property)); 
		#$tempString = $property;

		

		/*
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

	# Shorten string when characters > 30
	private function shortstring($string) {

		if (strpos($string, ' - ') !== false) {
			$stringArr = preg_split('/( - )/', $string);
			while (strlen($string) > 30) {
				array_pop($stringArr);
				$string = implode(' ', $stringArr);
			}
		}
		if (strpos($string, ' - ') == false && strlen($string) > 30) {
			$stringArr = explode(' ', $string);
			while (strlen($string) > 30) {
				array_pop($stringArr);
				$string = implode(' ', $stringArr);
			}
		}

		return $string;
	}
	
	private function createAds($title, $type, $performer) {
		//Get first artist in the list
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

		// Determine if genre of performance has close match with predefined genres
		foreach ($pLabel as $tempGenre => $value) {
			if (strpos($genre, $tempGenre) > 1) {
				$tLabel = $pLabel->$tempGenre;
				$genre = $tempGenre;
			} else {
				$tLabel = array('voorstelling', 'een voorstelling');
			}
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
		$replace = array('[performer]', '[title]', '[genre]', '[genreTerm]', '[venue]', '[location]', '[date]', '[dateFull]');

		# Add keyword insertion when performers amount > maxPerformers
		if (count($this->performers) > $this->maxPerformers && $type == 'performer') {
			//Sort performers by length for keyword insertion
			usort($this->performers,'sortByLength');
			# Placeholder for longest keyword
			$title = '{KeyWord:'.$this->performers[0].'}';
		}

		if ($type == 'title' || ($type == 'performer' && count($this->performers) <= $this->maxPerformers)) {
			$replacement = array($performer, $this->shortstring($title), $tLabel[1], $tLabel[0], $this->venue[0], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		} else {
			$replacement = array($performer,  $this->shortstring($this->title), $tLabel[1], $tLabel[0], $this->venue[0], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		}

		# Decode ads template in JSON format and iterate
		foreach (json_decode($this->template) as $template) {
			# Choose template that has the same type as the type argument
			if ($template->type == $type) {

				# Iterate ads template
				foreach($template->ads as $adkey => $adProperties) {


					#### CREATE ADD CONTENT ####

					//Sort first heading length from short to long. Needed to iterate them to fit the 30 characters.
					foreach ($adProperties->heading1 as $heading1) {
						$heading = $this->replaceTpl($heading1, $replace, $replacement, 30);

						# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
						if (strlen($heading) <= 30 || (count($this->performers) > $this->maxPerformers && strlen($heading) <= 40)) {
							$ad[$adkey]->heading[0] = $heading;
						}

					}

					//Sort second heading length from short to long. Needed to iterate them to fit the 30 characters.
					foreach ($adProperties->heading2 as $heading2) {
						$heading = $this->replaceTpl($heading2, $replace, $replacement, 30);

						# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
						if (strlen($heading) <= 30 || (count($this->performers) > $this->maxPerformers && strlen($heading) <= 40)) {
							$ad[$adkey]->heading[1] = $heading;
						}

					}
					
					//Sort third heading length from short to long. Needed to iterate them to fit the 30 characters.
					foreach ($adProperties->heading3 as $heading3) {
						$heading = $this->replaceTpl($heading3, $replace, $replacement, 30);

						# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
						if (strlen($heading) <= 30 || (count($this->performers) > $this->maxPerformers && strlen($heading) <= 40)) {
							$ad[$adkey]->heading[2] = $heading;
						}

					}

					// Remove first ad description with performer variable if performer doesn't exist
					if (strlen($performer) <= 0) {
						foreach($adProperties->description1 as $key => $value) {
							if (strpos($value, '[performer]') !== false) {
								unset($adProperties->description1[$key]);
							}
						}
					}

					// Remove second ad description with performer variable if performer doesn't exist
					if (strlen($performer) <= 0) {
						foreach($adProperties->description2 as $key => $value) {
							if (strpos($value, '[performer]') !== false) {
								unset($adProperties->description2[$key]);
							}
						}
					}

					//Sort first description length from short to long. Needed to iterate them to fit the 90 characters.
					foreach ($adProperties->description1 as $description1) {
						$description = $this->replaceTpl($description1, $replace, $replacement, 90);

						# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
						if (strlen($description) <= 90 || (count($this->performers) > $this->maxPerformers && strlen($description) <= 100)) {
							$ad[$adkey]->description[0] = $description;
						}

					}

					//Sort second description length from short to long. Needed to iterate them to fit the 90 characters.
					foreach ($adProperties->description2 as $description2) {
						$description = $this->replaceTpl($description2, $replace, $replacement, 90);

						# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
						if (strlen($description) <= 90 || (count($this->performers) > $this->maxPerformers && strlen($description) <= 100)) {
							$ad[$adkey]->description[1] = $description;
						}

					}

					#### CREATE DISPLAY URL ####

					// Determine if keyword insertion has been used in the display url
					$keywordInsertionPath = false;

					
					if (count($this->performers) > $this->maxPerformers && $type == 'performer') {
						if (strlen($title) <= 25) {
							$ad[$adkey]->path[0] = $this->removeChars(strtolower($genre));
							$ad[$adkey]->path[1] = $title;
							$keywordInsertionPath = true;
						}
					} 

					if ($keywordInsertionPath == false) {


						// Replace path title if keyword insertion couldn't be applied
						if (count($this->performers) > $this->maxPerformers && $type == 'performer') {
							if (strlen($this->title)>1) {
								$pathTitle = $this->title;
							} else {
								$pathTitle = $this->subtitle;
							}
						}

						else if (count($this->performers) <= $this->maxPerformers && $type == 'performer') {
							$pathTitle = $performer;
						
						} else {
							// If adgroup type is performer or title without keyword insertion being used
							$pathTitle = $title;
						}

						$pathString = strtolower(trim(preg_replace('/( de | een | en | het |:|, | & )/', ' ', trim($pathTitle))));

						// Check if pathString length <= 15 characters
						if (strlen($pathString) <= 15) {
							// Only use first genre if there are more
							if (strpos($genre, '_') > 1)  {
								$genre = explode('_', $genre)[0];
							}
							$ad[$adkey]->path[0] = $this->removeChars(strtolower($genre));

							// Remove unnecessary characters and replace spaces with dashes
							$ad[$adkey]->path[1] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathString)));
						}

						// Check if pathstring has more than 15 characters and split the words to the path fields
						else if (strlen($pathString) > 15) {

							$pathFits = false;

							// Split by space and dash
							$pathElements = preg_split('/( | - )+/i', trim(preg_replace('/(\d{1,7})/', '', $pathString)));

							// Determine if path string now only consists of one path
							if (count($pathElements) == 1) {
								$ad[$adkey]->path[0] = $this->removeChars(strtolower($genre));
								$ad[$adkey]->path[1] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathElements[0])));
								$pathFits = true;
							} else {

								// Devide path string over 2 paths for display url
								for ($i = 0; $i < 2; $i++) {
									$ad[$adkey]->path[$i] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathElements[$i])));
								}

							}

							//If both paths still have more than 15 characters, split them by their inner parts
							if (strlen($ad[$adkey]->path[0]) > 15) {
								$wordSplit = json_decode(file_get_contents("https://picturage.nl/intk/theaterads/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$adkey]->path[0]));
								if (strlen($wordSplit[0]) <= 15 && strlen($wordSplit[0]) > 1) {
									$ad[$adkey]->path[0] = $wordSplit[0];
									$ad[$adkey]->path[1] = $wordSplit[1];
								}
							}

							if (strlen($ad[$adkey]->path[1]) > 15) {
								$wordSplit = json_decode(file_get_contents("https://picturage.nl/intk/theaterads/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$adkey]->path[1]));
								if (strlen($wordSplit[0]) <= 15 && strlen($wordSplit[0]) > 1) {
									$ad[$adkey]->path[0] = $wordSplit[0];
									$ad[$adkey]->path[1] = $wordSplit[1];
								}
							}

						}
					}


					if (strlen($ad[$adkey]->path[0]) > 15) {
						$ad[$adkey]->path[0] = $tLabel[0];
					}


					if (strlen($ad[$adkey]->path[0]) < 1) {
						$ad[$adkey]->path[0] = $tLabel[0];
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