<?php
header('Content-Type: application/json; charset=utf-8');
#ini_set('display_errors', 1);

class Keywords {
	private $charLimit;
	private $lexicon;
	public function __construct($title, $venue, $city, $placements, $type) {
		// Don't use title longer than 80 characters

		$this->type = $type;
		$this->title = $title;
		$this->charLimit = 30;
		$this->lexicon = new Lexicon();
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

	private function stringParts($matches, $input) {

		// Loop delimiters and split string
		$delOutputArray = array();
		for($key = 0; $key <= count($matches[0]); $key++) {
			if ($key == 0) {
				$start = 0;
				$length = $matches[0][$key][1];
				$delimiter = $matches[0][$key][0];
			}

			else if ($key == count($matches[0])) {
				$delimiter = $matches[0][$key-1][0];
				$start = $matches[0][$key-1][1]+strlen($delimiter);
				$length = strlen($input);
			}

			else {
				$delimiter = $matches[0][$key-1][0];
				$start = $matches[0][$key-1][1]+strlen($delimiter);
				$length = $matches[0][$key][1]-$start;
			}

			// Output string parts and its start position, length and delimiters
			array_push($delOutputArray, array(
				"delimiter"=>$delimiter,
				"startPos"=>$start, 
				"endPos"=>$length,
				"string"=>trim(substr($input, $start, $length)),
				"length"=>strlen(trim(substr($input, $start, $length)))
			));
		}
		return $delOutputArray;
	}

	private function splitString($fullString, $stringParts, $maxLength) {
		$partsAmount = count($stringParts);
		$key = 0;
		$output = array();
		$outputKey = 0;
		$merge = '';
		// Defines the actual key of each string element, before the splitting.
		$keyOrig = 0;

		while ($partsAmount >= 0) {
			$partsAmount--;

			//Only merge string when character length <= character limit
			if (isset($stringParts[$key]['string'])) {
				// Merge string parts
				$merge .= $stringParts[$key]['string'].' ';
			}

			if (strlen(trim($merge)) <= $maxLength) {


				// Check if end of original string has been reached
				if ($keyOrig >= count($stringParts)-1) {
					// If end is reached, remove last space from string
					$output[$outputKey] = trim($merge);
				} else {
					$output[$outputKey] = $merge;
				}

			} else {

				// If first string part ends with an unnecessary <= 3 characters word, remove that word
				$valSplit = explode(' ', $output[$outputKey]);

				// Loop splitted string values in descending order until string end doesn't unnecessary contain words with <=3 character length
				$rightEndFound = false;
				$uWordCount = 0;

				for ($i = count($valSplit); $i >= 0; $i--) {
					if (array_key_exists($i, $valSplit) == false || (array_key_exists($i, $valSplit) && !preg_match("/(big|bad|job|jam|joy|max|mol)/i", $valSplit[$i], $matches) && strlen($valSplit[$i]) <= 3) && $rightEndFound == false) {
						$uWordCount++;
						unset($valSplit[$i]);
					} else {
						$rightEndFound = true;
					}
				}
				// Assign revised string
				$output[$outputKey] = implode(' ', $valSplit);

				// Set key to string part before last removed word
				$key -= $uWordCount;
				// Extend while loop with amount of unnecessary words at the end of string part 1
				$partsAmount .= $uWordCount;




				$merge = '';
				$outputKey++;
			}
			$key++;
			$keyOrig++;
		} 
		return $output;
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
		if (preg_match_all('/(, | and |&| - | i.s.m. | ism | ft. | feat. | -- | met |: |\/)/', $input, $matches, PREG_OFFSET_CAPTURE) && strlen($input) > $this->charLimit) {

			//echo $input."\n";

			$delOutputArray = $this->stringParts($matches, $input);

			// Merge string parts if they fit the max character limit
			foreach($delOutputArray as $key => $delElement) {
				$outputString = $delElement['string'];
				if ($key < count($delOutputArray)-1) {
					$merged = trim(substr($input, $delElement['startPos'], $delOutputArray[$key+1]['endPos']+$delOutputArray[$key+1]['startPos']));

					if (strlen($merged) <= $this->charLimit && strpos($merged, ',') === false) {
						$outputString = $merged;
						$delOutputArray[$key+1]['merged'] = true;

					}


				}

				#print_r($delOutputArray);

				$delOutput[$key] = $delOutputArray[$key]['string'];

				/*
				if (array_key_exists('merged', $delElement) == false || array_key_exists('merged', $delElement) == true && $delElement['merged'] == false) {
				$delOutput[$key] = $outputString;
				}
				*/

			}

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

		// Split title into ad group names
		foreach ($delOutput as $delString) {
			$delString = trim($delString);

			// Determine if string is written in capitals or in lowercase
			if (strtoupper($delString) == $delString || strtolower($delString) == $delString) {
				// Change capitalization to title case
				$delString = ucwords(strtolower($delString));
			}

			// Split by predifined delimiters if string length is more than character limit
			if (preg_match_all('/( )/', $delString, $matches, PREG_OFFSET_CAPTURE) && strlen($delString) > $this->charLimit /*&& count($delOutput) <= 1*/) {

				$outputArray = array_merge($outputArray, $this->splitString($delString, $this->stringParts($matches, $delString), $this->charLimit));

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

			//Remove prepositions on start or end of keyword string
			$splitTempKeyword = explode(' ',$tempKeyword);
			$firstWord = $splitTempKeyword[0];
			$lastWord = $splitTempKeyword[count($splitTempKeyword)-1];
			if (in_array($firstWord, $this->lexicon->prepositions) || in_array($lastWord, $this->lexicon->prepositions)) {
				$tempKeyword = trim(str_replace($this->lexicon->prepositions, "", $tempKeyword));
			}

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
				if (isset($_GET['splitKeywords']) && $_GET['splitKeywords'] == 'true') {
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

#$keywordsObj = new Keywords("Mini-college van Pieter Roelofs, hoofd Schilder- en Beeldhouwkunst van het Rijksmuseum", array("Stadsgehoorzaal"), "Leiden", array("concert", "muziek", "live"), "title");
#print_r($keywordsObj);
#echo memory_get_usage();
#echo json_encode($keywordsObj);
?>