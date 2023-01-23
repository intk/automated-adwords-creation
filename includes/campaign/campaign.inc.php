<?php
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

// Include keywords module
include('includes/methods/keywords/keywords.inc.php');

//Contains name, date, target location, sitelink extentions, AdGroups, Ads, keywords
class Campaign {
	private $template;
	private $lexicon;
	private $price;
	private $title;
	private $subtitle;
	private $venue;
	private $city;
	private $performers;
	private $link;
	private $maxPerformers;
    public function __construct($production, $template, $lang) {

		//Parse campaign info
		$itemDate = $production->date->time;
		$this->template = $template;
		$this->lexicon = new Lexicon();
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
		// Add language to campaign name
		if (strpos($lang, 'nl') === false) {
			$this->name = '['.strtoupper($lang).'] '.$this->name;
		} 
		$this->language = $lang;
		$this->venue = $production->venue;
		if (array_key_exists(1, $this->venue) == false) {
			$this->venue[1] = $this->venue[0];
		} 
		$this->city = $production->location;
		$this->genre = $production->genre;
		if (isset($production->performers)) {
			$this->performers = $production->performers;
		} else {
			$this->performers = false;
		}
		// Define date as object
		$this->date = new stdClass();
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

		//Remove number and plus sign from string, only when plus sign is present
		if (preg_match("/\d\+/", $string, $match)) {
			$output = str_replace('+', '', preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'‘’&,-.:]+/u", " ", str_replace($replace, $replacement, $string)))));
		} else {
			$output = str_replace('+', '', preg_replace('/[\[{\(].*[\]}\)]/u', '', trim(preg_replace("/[^\p{L}()'‘’&,-.:[0-9]]+/u", " ", str_replace($replace, $replacement, $string)))));
		}

		// Determine if string is written in capitals or in lowercase
		if (strtoupper($output) == $output || strtolower($output) == $output) {
			// Change capitalization to title case
			$output = ucwords(mb_strtolower($output, 'UTF-8'));
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
		$monthAbbr = $this->lexicon->monthAbbr;
		$monthFull = $this->lexicon->monthFull;
		$monthEN = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
		$day = date('d', $time);
		if ($day < 10) { $day = substr($day, 1); }
		$output[0] = date('M d, Y', $time);


		// Determine if custom date format is given. Use custom date format
		if (isset($this->lexicon->dateFormatShort)) {
			$output[1] = str_replace(array('%DD', '%MM', '%m', '%Y'), array($day, str_ireplace($monthEN, $monthFull, date('M', $time)), date('n', $time), date('Y', $time)), $this->lexicon->dateFormatShort);
		} else {
			$output[1] = $day.' '.str_ireplace($monthEN, $monthAbbr, date('M', $time));
		}

		$output[2] = str_replace(array('%DD', '%MM'), array($day, str_ireplace($monthEN, $monthFull, date('M', $time))), $this->lexicon->dateFormat);

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

		//Get keyword placements from lexicon file
		$placements = $this->lexicon->keyword;

		if (count($this->genre) > 1) {
			$genre = $this->genre[1];
		} else {
			$genre = $this->genre[0];
		}

		$placementsList = array();

		//Loop keyword list
		foreach($placements as $keyWord => $keyValue) {
			//Check if genre has a keyword
			if (stripos($genre, $keyValue) !== false) {

				// Add keyword placements for specific genres
				if ($keyWord == 'cabaret' || $keyWord == 'lecture' || $keyWord == 'family' || $keyWord == 'dance') {
					$placementsList = array($keyValue, $placements->theater, $placements->tickets);
				} 
				if ($keyWord == 'concert' || $keyWord == 'music') {
					$placementsList = array($placements['concert'], $placements['music'], $placements['live'], $placements['tickets']);
				}
				else {
					$placementsList = array($keyValue, $placements['tickets']);
				}
			}
		}


		# Add default placements if placement list is still empty
		if (count($placementsList) == 0) {
			$placementsList = array($genre, $placements['tickets']);
		}

		$keywordsObj = new stdClass();
		$keywordsObj->adgroup = array();
		$manyPerformers = false;
		$performersList = false;

		// If this is true, don't make another ad group from the title
		if ($this->performers !== false && count($this->performers) > 0) {
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

			if ($this->performers !== false && count($this->performers) > 1) {
				array_push($titlePlacementList, $this->performers[0]);
			}

			$titleObj = new Keywords($this->title, $this->venue, $this->city, $titlePlacementList, 'title');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);
		}

		// Create keywords and adgroup of subtitle
		if (strlen($this->subtitle) > 1) {
			$subtitleObj = new Keywords($this->subtitle, $this->venue, $this->city, $placementsList, 'performer');
			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $subtitleObj->adgroup);
		}

		if ($this->performers !== false && count($this->performers) > 0 && count($this->performers) <= $this->maxPerformers && $manyPerformers == false) {
			foreach ($this->performers as $performer) {
				$performersObj = new Keywords($performer, $this->venue, $this->city, $placementsList, 'performer');
				$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $performersObj->adgroup);
			}
			$manyPerformers = true;
		}

		// Create keywords and adgroups of title + genre if < 2 adgroups created
		if (count($keywordsObj->adgroup) < 2 && $performersList == false) {

			//

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

			$titleObj = new Keywords($newElement, $this->venue, $this->city, $placementsList, 'title');

			// Remove duplicate keywords
			foreach ($titleObj->adgroup[0]['keywords'] as $newKeyword) {
				$key = array_search($newKeyword, $keywordsObj->adgroup[0]['keywords']);
				if ($key !== false) {
					unset($keywordsObj->adgroup[0]['keywords'][$key]);
				}
			}


			# Merge ad groups from keyword object
			$keywordsObj->adgroup = array_merge($keywordsObj->adgroup, $titleObj->adgroup);

		}

		foreach ($keywordsObj->adgroup as $key => $adgroup) {
			$this->adgroup[$key] = new stdClass();
			$this->adgroup[$key]->name = $adgroup['name'];
			$this->adgroup[$key]->type = $adgroup['type'];

			// Determine ad group type
			if ($adgroup['name'] == $this->title) {
				$this->adgroup[$key]->type = 'title';
			}

			if (strlen(trim($this->subtitle)) > 1) {
				$subtitle = $this->trimArtist($this->subtitle)[0];
			} 

			// If subtitle is empty use first performer as subtitle
			else if (strlen(trim($this->subtitle)) < 1 && $this->performers !== false && count($this->performers) > 0) {
				$subtitle = $this->performers[0];
			} else {
				$subtitle = '';
			}

			$this->adgroup[$key]->ad = $this->createAds(trim($this->adgroup[$key]->name), $this->adgroup[$key]->type, $subtitle);
			$this->adgroup[$key]->keywords = $adgroup['keywords'];
		}

		// Create keywords and adgroups of performers list
		if ($this->performers !== false && count($this->performers) > $this->maxPerformers && $manyPerformers == false) {
			$adGroupCount = count($this->adgroup);
			$this->adgroup[$adGroupCount] = new stdClass();
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

		// Determine if venue can be shortened if string doesn't fit within max characters
		if (strlen($tempString) > $Maxcharacters) {
			$replacement[5] = $replacement[6];
			$tempString = ucfirst(str_replace($replace, $replacement, $property));
		}


		

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
		
		//Get genre placements from lexicon file
		$pLabel = $this->lexicon->adPlacement;
	
		$genre = $this->genre[0];

		// Determine if genre of performance has close match with predefined genres
		$foundGenre = false;
		foreach ($pLabel as $key => $tempGenre) {

			if (stripos($genre, $tempGenre[0]) > -1) {
				$tLabel = $tempGenre;
				$genre = $tempGenre[0];
				$foundGenre = true;
			} else {
				if (!$foundGenre) {
					$tLabel = $pLabel->performance;
				}
			}
		}
		
		$hDate = $this->date->AdDate;

		# If performer ad group, change swap title and performer attribute
		if ($type == 'performer') {
			$performer = $title;
			$title = $this->title;
		}


		# Template replacements
		$replace = array('[performer]', '[title]', '[genre]', '[genreSentence]', '[genreTerm]', '[venue]', '[venueShort]', '[location]', '[date]', '[dateFull]');

		# Add keyword insertion when performers amount > maxPerformers
		if ($this->performers !== false && count($this->performers) > $this->maxPerformers && $type == 'performer') {
			//Sort performers by length for keyword insertion
			usort($this->performers,'sortByLength');
			# Placeholder for longest keyword
			$performer = '{KeyWord:'.$this->performers[0].'}';
		}

		if ($type == 'title' || $type == 'performer' && ($this->performers !== false && count($this->performers) <= $this->maxPerformers)) {
			$replacement = array($performer, $this->shortstring($title), $tLabel[1], $tLabel[2], $tLabel[0], $this->venue[0], $this->venue[1], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		} else {
			$replacement = array($performer,  $this->shortstring($this->title), $tLabel[1], $tLabel[2], $tLabel[0], $this->venue[0], $this->venue[1], $this->city, $this->date->AdDate, $this->date->AdDateFull);
		}

		# Decode ads template in JSON format and iterate
		foreach (json_decode($this->template) as $template) {
			# Choose template that has the same type as the type argument
			if ($template->type == $type) {

				#### CREATE ADD CONTENT ####

				# Iterate ads template
				foreach($template->ads as $adkey => $adProperties) {

					// Iterate through each ad field
					$headingnum = 0;
					$descriptionnum = 0;
					foreach ($adProperties as $property => $value) {

						//Sort heading field length from short to long. Needed to fit the 30 characters.
						if (strpos($property, 'heading') > -1) {

							// Remove first ad description with genre variable if genre doesn't exist
							if (strlen($tLabel[1]) <= 0) {
								foreach($value as $key => $line) {
									if (strpos($line, '[genre]') !== false) {
										unset($value[$key]);
									}
								}
							}

							foreach ($value as $headingString) {
								$heading = $this->replaceTpl($headingString, $replace, $replacement, 30);

								# Assign template text to ad headline if it fit the 30 characters, excluding keyword insertion variable
								if (strlen($heading) <= 30 || ($this->performers !== false && count($this->performers) > $this->maxPerformers && stripos($heading, '{KeyWord:') > -1 && strlen($heading) <= 40)) {
									$ad[$adkey]->heading[$headingnum] = $heading;
								}

							}
							$headingnum++;

						}


						//Sort description field length from short to long. Needed to fit the 90 characters.
						if (strpos($property, 'description') > -1) {
							$descriptionArray = $value;

							// Remove first ad description with performer variable if performer doesn't exist
							if (strlen($performer) <= 0) {
								foreach($descriptionArray as $key => $value) {
									if (strpos($value, '[performer]') !== false) {
										unset($descriptionArray[$key]);
									}
								}
							}

							// Remove first ad description with genre variable if genre doesn't exist
							if (strlen($tLabel[1]) <= 0) {
								foreach($descriptionArray as $key => $value) {
									if (strpos($value, '[genre]') !== false) {
										unset($descriptionArray[$key]);
									}
								}
							}

							//Sort first description length from short to long. Needed to iterate them to fit the 90 characters.
							foreach ($descriptionArray as $descriptionString) {

								$description = $this->replaceTpl($descriptionString, $replace, $replacement, 90);
								# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
								if (strlen($description) <= 90 || ($this->performers !== false && count($this->performers) > $this->maxPerformers && strlen($description) <= 100)) {
									$ad[$adkey]->description[$descriptionnum] = $description;
								}
								// Try to add the full title to the description if it fits the 90 characters
								$tempReplacement = $replacement;
								$tempReplacement[1] = $this->title;
								$description = $this->replaceTpl($descriptionString, $replace, $tempReplacement, 90);
								# Assign template text to ad description if it fit the 90 characters, excluding keyword insertion variable
								if (strlen($description) <= 90 || ($this->performers !== false && count($this->performers) > $this->maxPerformers && strlen($description) <= 100)) {
									$ad[$adkey]->description[$descriptionnum] = $description;
								}

							}
							$descriptionnum++;
						}

					}

					#### CREATE DISPLAY URL ####

					// Determine if keyword insertion has been used in the display url
					$keywordInsertionPath = false;

					
					if ($this->performers !== false && count($this->performers) > $this->maxPerformers && $type == 'performer') {
						if (strlen($title) <= 25) {
							$ad[$adkey]->path[0] = $this->removeChars(strtolower($genre));
							$ad[$adkey]->path[1] = mb_strtolower($title, 'UTF-8');
							$keywordInsertionPath = true;
						}
					} 

					if ($keywordInsertionPath == false) {


						// Replace path title if keyword insertion couldn't be applied
						if ($this->performers !== false && count($this->performers) > $this->maxPerformers && $type == 'performer') {
							if (strlen($this->title)>1) {
								$pathTitle = $this->title;
							} else {
								$pathTitle = $this->subtitle;
							}
						}

						else if (($this->performers !== false && count($this->performers) <= $this->maxPerformers || $this->performers == false) && $type == 'performer') {
							$pathTitle = $performer;
						
						} else {
							// If adgroup type is performer or title without keyword insertion being used
							$pathTitle = $title;
						}

						$pathString = mb_strtolower(trim(preg_replace('/( de | een | en | het |:|, | & )/', ' ', trim($pathTitle))), 'UTF-8');
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

								// Divide path string over 2 paths for display url
								$pathNum = 0;
								$i = 0;
								while ($i < count($pathElements)) {

									// Don't split when a path part contains more than 15 characters
									if (strlen($pathElements[$i]) > 15) {
										$ad[$adkey]->path[0] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathElements[0])));
										$ad[$adkey]->path[1] = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', $pathElements[1])));
									} else {
										// Divide string parts between 2 paths of max 15 characters
										if ($pathNum < 2) {
											$tempString = $this->removeChars(str_replace('--', '-', str_replace(' ', '-', trim($ad[$adkey]->path[$pathNum].' '.$pathElements[$i]))));

											if (strlen($tempString) <= 15) {
												$ad[$adkey]->path[$pathNum] = $tempString;
											} else {
												$pathNum++;
												$i--;
											}
										}
									}

									$i++;
								}

							}

							//If both paths still have more than 15 characters, split them by their inner parts
							if (strlen($ad[$adkey]->path[0]) > 15) {
								$wordSplit = json_decode(file_get_contents("https://localhost/adwords-automation/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$adkey]->path[0]));
								if (strlen($wordSplit[0]) <= 15 && strlen($wordSplit[0]) > 1) {
									$ad[$adkey]->path[0] = $wordSplit[0];
									$ad[$adkey]->path[1] = $wordSplit[1];
								}
							}

							if (strlen($ad[$adkey]->path[1]) > 15) {
								$wordSplit = json_decode(file_get_contents("https://localhost/adwords-automation/includes/methods/dictionary/dictionary.processor.php?word=".$ad[$adkey]->path[1]));
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
			
		print_r($ad);
		return $ad;
	}
	
	public function toDatabase($theaterId) {
		return "(theaterId, title, subtitle, genre, performanceDate, creationDate, link) VALUES (".$theaterId.", '".$this->title."', '".$this->subtitle."', '".implode(';', $this->genre)."', '".$this->date->time."', '".time()."', '".$this->link."')";
	}
}


?>