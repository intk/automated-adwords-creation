<?php
header('Content-Type: application/json');

class Keywords {
	public function __construct($title, $venue, $city, $placements, $type) {

		$this->type = $type;
		$this->title = $title;
		$this->venue = $venue;
		$this->city = $city;
		$this->placements = $placements;
		$this->newAdgroup = $this->newAdgroup();
		$this->adgroup = $this->keywordsParser();
	}

	private function newAdgroup() {

		// Derive string 
		$input = $this->title;

		// Determine if a new adgroup should be created for each keyword or not
		if (strlen($input) > 20) {
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
		if (strpos($this->title, ': ') !== false) {
			$input = preg_replace("/(\w*: )/", '', $input);
		}

		// Parse by predifined delimiters
		if (strpos($input, ',') !== false || (strpos($input, ',') !== false && strpos($input, ',') < strpos($input, ' en '))) {
			$delOutput = preg_split('(,| en )', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' en ') !== false && strpos($input, '&') !== false) {
			$delOutput = preg_split('(,| & )', $input);
			$this->newAdgroup = true;
		}
		else if (strlen($input) > 20 && strpos($input, ' en ') !== false) {
			$delOutput = preg_split('/(,| en )/', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' en ') === false && strpos($input, ' & ') !== false && strlen($input) > 30) {
			$delOutput = explode(' & ', $input);
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
			$delOutput = preg_split('/(, | i.s.m. | ism | ft. | feat. | -- | met )+/i', $input);
			if (count($delOutput) > 1) {
				$this->newAdgroup = true;
			}
		}

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
			if (strlen($delString) > 20) {

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

					if (strlen(trim($tempString)) < 30) {
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
/*
$keywordsObj = new Keywords("Peter Bla - Blasr", "Botanique", "Brussel", array("concert", "muziek", "live"));
print_r($keywordsObj);
echo json_encode($keywordsObj);
*/
?>