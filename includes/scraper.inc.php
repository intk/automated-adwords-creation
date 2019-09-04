<?php

//Contains name & programme
class Theater {
    public function __construct($name, $month, $venue, $lang) {
		include('config.inc.php');
		//Set client character set to utf-8
		mysqli_query($connect, "SET NAMES 'utf8'");
		//Obtain database records
		$query = mysqli_query($connect, "SELECT * FROM theaters WHERE alias LIKE '%".$name."%'");
		//Check if theater exists in database
		if (mysqli_num_rows($query) < 1) {
			$this->success = "false";
		} else {
			$this->success = "true";
			$result = mysqli_fetch_array($query);
			$this->name = $result['name'];
			$this->method = $result['method'];
			
			//Add different location formats
			$location['city'] = $result['location'];
			$location['venue'][0] = $result['name'];
			if (strlen($result['shortName']) > 1) {
				$location['venue'][1] = $result['shortName'];
			}
			//'exclude' parameter for preventing irrelevant campaign creation
			$this->productions = $this->scrape($result['method'], $result['url'], $result['tags'], $location, $lang, $month, $venue, $result['exclude']);

		}
    }
	
	//Scrape programme
	private function scrape($method, $url, $tags, $location, $lang, $month, $venue, $exclude) {
		include('methods/'.$method.'/'.$method.'.processor.php');
		return $productions;
	}
}
?>