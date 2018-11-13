<?php
header('Content-Type: application/json');

class Keywords {
	public function __construct($title, $venue, $city, $placements) {
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
		if (strlen($input) > 30) {
			return 'true';
		} else {
			return 'false';
		}
	}

	private function keywordsParser() {

		// Derive string 
		$input = $this->title;

		// Parse by predifined delimiters
		if (strpos($input, ',') !== false || (strpos($input, ',') !== false && strpos($input, ',') < strpos($input, ' en '))) {
			$delOutput = preg_split('(,| en )', $input);
			$this->newAdgroup = true;
		}
		else if (strpos($input, ' en ') !== false && strpos($input, '&') !== false) {
			$delOutput = preg_split('(,| & )', $input);
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
			$delOutput =  preg_split('/(, | i.s.m. | ism | ft. | feat. | -- | met )+/i', $input);
			if (count($delOutput) > $delOutputCount) {
				$this->newAdgroup = true;
			}
		}

		$outputArray = array();
		
		// Parse by every second space
		foreach ($delOutput as $delString) {

			// Only parse by every second space if string length is more than 20 characters
			if (strlen($delString) > 20) {

				// Remove words that have less than 4 characters
				$tempVal = explode(' ', $delString);
		    	foreach($tempVal as $key => $val) {
		    		//Exclude words that can be part of a name
		    		if (!preg_match("/(big|job|jam|joy|max)/", $val, $matches) && strlen($val) < 4) {
		    			unset($tempVal[$key]);
		    		}
		    	}
		    	// Only implode space when more than 1 space occurs in string
		    	if (substr_count($delString, " ") > 1) {
		    		$delString = implode(' ', $tempVal);
		    	}


				// Split by second space
				$deliverPair = array_map(
				    function($value) {
				    	
				        return implode(' ', $value);
				    },
				    array_chunk(
				        explode(' ', $delString),
				        2
				    )
				);

				$outputArray = array_merge($outputArray, $deliverPair);
			} else {
				array_push($outputArray, $delString);
			}

		}
		$tempOutputArray = $outputArray;
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
				$DuplicateKeywords = array_fill(0, count($placements), $tempKeyword);



				// Placements will be placed after the keyword, so it becomes a new keyword
				$outputArray[$key] = array_merge(array("name"=>$tempKeyword,"type"=>"artist", "keywords"=>array_merge(array(strtolower($tempKeyword)), array_map(
					function($placement, $keyword) {
						return strtolower($keyword .' '.$placement);
					}, $placements, $DuplicateKeywords
				
				))));

				// Remove any single word keywords
				foreach ($outputArray[$key]['keywords'] as $keyword => $value) {
					if (substr_count($value, " ") < 1) {
						unset($outputArray[$key]['keywords'][$keyword]);
					}
				}


			} else {
				// Determine if there are any single word keywords
				if (substr_count($tempKeyword, " ") < 1) {
					$outputArray[$key] = '+ '.$tempKeyword;
					$outputArray[count($tempOutputArray)] = $tempKeyword.' '.$this->city;
				}
			}
		}

		// Make adgroup object
		if ($this->newAdgroup == 'false') {
			$outputArrayTemp = $outputArray;
			$outputArray = null;
			$outputArray[0] = array("name"=>$this->title,"type"=>"title", "keywords"=>$outputArrayTemp);
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