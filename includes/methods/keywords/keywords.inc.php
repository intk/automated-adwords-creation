<?php
header('Content-Type: application/json');

class Keywords {
	private $charLimit;
	public function __construct($title, $venue, $city, $placements, $type) {

		$this->type = $type;
		$this->title = $title;
		$this->charLimit = 30;

		// Use short venue name when character length of venue name is >= Character Limit
		if (strlen($venue[0]) > $this->charLimit && array_key_exists(1, $venue)) {
			$this->venue = $venue[1];
		} else {
			$this->venue = $venue[0];
		}
		$this->city = $city;
		$this->placements = $placements;
		$this->newAdgroup = $this->newAdgroup();
		$this->adgroup = $this->keywordsParser();
	}

	private function newAdgroup() {

		// Derive string 
		$input = $this->title;

		// Determine if a new adgroup should be created for each keyword or not
		if (strlen($input) >= $this->charLimit) {
			return 'true';
		} else {
			return 'false';
		}
	}

	private function keywordsParser() {

		// Derive string 
		$input = $this->title;
		if (strpos($this->title, 'met ') !== false) {

			$input = str_replace('met ', '', $input);
		}
		/*
		if (strpos($this->title, ': ') !== false) {
			$input = preg_replace("/(\w*: )/", '', $input);
		}
		*/

		// Split by predifined delimiters.

		if (preg_match_all('/(, | en | and |&| - | i.s.m. | ism | ft. | feat. | -- | met |:)/', $input, $matches, PREG_OFFSET_CAPTURE) && strlen($input) > $this->charLimit) {

			// Loop delimiters and split string
			echo $input."\n";

			$delOutputArray = array();
			for($key = 0; $key <= count($matches[0]); $key++) {
				if ($key == 0) {
					$start = 0;
					$length = $matches[0][$key][1];
					$delimiter = $matches[0][$key][0];
				}

				else if ($key == count($matches[0])) {
					$start = $matches[0][$key-1][1]+1;
					$length = strlen($input);
					$delimiter = $matches[0][$key-1][0];
				}

				else {
					$start = $matches[0][$key-1][1]+1;
					$length = $matches[0][$key][1]-$start;
					$delimiter = $matches[0][$key-1][0];
				}

				// Output string parts and its start position, length and delimiters
				array_push($delOutputArray, array(
					"delimiter"=>$delimiter,
					"startPos"=>$start, 
					"endPos"=>$length,
					"string"=>trim(substr($input, $start, $length)),
					"length"=>strlen(trim(substr($input, $start, $length))),
				));
			}

			// Merge string parts if they fit the max character limit
			foreach($delOutputArray as $key => $delElement) {
				print_r($delElement);
				$outputString = $delElement['string'];
				if ($key < count($delOutputArray)-1) {
					$merged = trim(substr($input, $delElement['startPos'], $delOutputArray[$key+1]['endPos']+$delOutputArray[$key+1]['startPos']));

					if (strlen($merged) <= $this->charLimit && strpos($merged, ',') === false) {
						$outputString = $merged;
						$delOutputArray[$key+1]['merged'] = true;

					}


				}

				$delElement = $delOutputArray[$key];
				if ($delElement['merged'] == false) {
					$delOutput[$key] = $outputString;
				}



			}

			print_r($delOutput);


			$this->newAdgroup = true;
		} else {

			$delOutput[0] = $input;
		}

		/*
		if (strpos($input, ',') !== false || (strpos($input, ',') !== false && strpos($input, ',') < strpos($input, ' en '))) {
			$delOutput = preg_split('/(,| en| - )/', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' en ') !== false && strpos($input, '&') !== false) {
			$delOutput = preg_split('/(,| & )/', $input);
			$this->newAdgroup = true;
		}
		else if (strlen($input) > 20 && strpos($input, ' en ') !== false) {
			$delOutput = preg_split('/(,| en)/', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' en ') === false && strpos($input, ' & ') !== false && strlen($input) > 20) {
			$delOutput = preg_split('/( - | & )/', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' and ') !== false && strlen($input) > 30) {
			$delOutput = explode(' and ', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' - ') !== false) {
			$delOutput = explode(' - ', $input);
			$this->newAdgroup = true;
		} else {
			$delOutputCount = count($delOutput);
			$delOutput = preg_split('/(, | i.s.m. | ism | ft. | feat. | -- | met |:)+/i', $input);
			if (count($delOutput) > 1) {
				$this->newAdgroup = true;
			}
		}
		*/
		
		$outputArray = array();

		// Parse by every second space
		foreach ($delOutput as $delString) {
			$delString = trim($delString);

			// Determine if string is written in capitals or in lowercase
			if (strtoupper($delString) == $delString || strtolower($delString) == $delString) {
				// Change capitalization to title case
				$delString = ucwords(strtolower($delString));
			}

			// Only parse by every second space if string length is more than 20 characters
			if (strlen($delString) > $this->charLimit) {

				// Remove words that have less than 4 characters
				$tempVal = explode(' ', $delString);
		    	foreach($tempVal as $key => $val) {
		    		//Exclude words from removal that can be part of a name
		    		if (!preg_match("/(big|job|jam|joy|max|van|der|tot|een|mol|op|)/i", $val, $matches) && strlen($val) < 4) {
		    			unset($tempVal[$key]);
		    		}
		    	}
		    	// Only implode space when more than 1 space occurs in string
		    	if (substr_count($delString, " ") > 1) {
		    		$delString = implode(' ', $tempVal);
		    	}

		    	$deliverPair = array();

				// Split by every 30 characters
				$i = 0;
				$tempString = '';
				foreach(explode(' ', $delString) as $delElement) {
					$tempString .= $delElement.' ';

					if (strlen(trim($tempString)) < $this->charLimit) {
						$deliverPair[$i] = trim($tempString);
					} else {
						$tempString = $delElement.' ';
						$i++;
						$deliverPair[$i] = trim($tempString);
					}
				}

				$outputArray = array_merge($outputArray, $deliverPair);

			} else {
				array_push($outputArray, $delString);
			}

		}
		$tempOutputArray = $outputArray;

		/*
		if (strpos($this->title, $this->placements[0]) !== false) {
			$tempOutputArray = array($this->title);
		}
		*/

		foreach ($tempOutputArray as $key => $tempKeyword) {

			// Create keywords for each new adgroup
			if ($this->newAdgroup == 'true' || count($tempOutputArray) == 1) {
				$this->newAdgroup = 'true';
				$placements = $this->placements;
				if (strlen($tempKeyword) < 20) {
					array_push($placements, $this->venue);
				}
				array_push($placements, $this->city);


				// Make sure there is the same amount of keywords, and placements to combine
				$DuplicateKeywords = array_fill(0, count($placements), trim($tempKeyword));

				// Split keyword by space
				if ($_GET['splitKeywords'] == 'true') {
					$placementsList = $placements;
					$splittedKeyword = explode(' ', $tempKeyword);
					foreach($splittedKeyword as $splitted) {
						if (is_numeric($splitted) == false && strlen($splitted) > 4 && strpos($tempKeyword, $this->placements[0]) == false) {
							$DuplicateKeywords = array_merge($DuplicateKeywords, array_fill(0, count($placements), trim($splitted)));
							$placementsList = array_merge($placementsList, $placements);
						}
					}
					if (count($placementsList) > 1) {
						$placements = $placementsList;
					}
				}

				// Placements will be placed after the keyword, so it becomes a new keyword
				$outputArray[$key] = array_merge(array("name"=>$tempKeyword,"type"=>$this->type, "keywords"=>array_merge(array(strtolower($tempKeyword)), array_map(
					function($placement, $keyword) {
						// Determine if placement doesn't partly match the keyword
						if (stripos($keyword, $placement) === false) {
							return strtolower($keyword .' '.$placement);
						} else {
							return strtolower($keyword);
						}
					}, $placements, $DuplicateKeywords
				
				))));

				// Remove duplicate keywords
				$outputArray[$key]['keywords'] = array_unique($outputArray[$key]['keywords']);

				// Remove any single word keywords
				/*
				foreach ($outputArray[$key]['keywords'] as $keyword => $value) {
					if (substr_count($value, " ") < 1) {
						unset($outputArray[$key]['keywords'][$keyword]);
					}
				}
				*/


			} else {
				// Determine if there are any single word keywords
				if (substr_count($tempKeyword, " ") < 1) {
					$outputArray[count($tempOutputArray)]['keywords'][0] = '+ '.$tempKeyword;
					$outputArray[count($tempOutputArray)]['keywords'][1] = $tempKeyword.' '.$this->city;
				}
			}
		}


		// Make adgroup object
		if ($this->newAdgroup == 'false') {
			$outputArrayTemp = $outputArray;
			$outputArray = array();
			$outputArray[0] = array("name"=>$this->title,"type"=>$this->type, "keywords"=>$outputArrayTemp);
		}

		return $outputArray;


	}
}

$keywordsObj = new Keywords("De Waanzinnige Boomhut van 13 Verdiepingen", array("Stadsgehoorzaal"), "Leiden", array("concert", "muziek", "live"), "title");
print_r($keywordsObj);
echo json_encode($keywordsObj);
?>