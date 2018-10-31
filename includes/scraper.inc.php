<?php

//Contains name & programme
class Theater {
    public function __construct($name, $month) {
		include('config.inc.php');
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
			$this->productions = $this->scrape($result['method'], $result['url'], $result['tags'], $result['location'], $month, $result['exclude']);
		}
    }
	
	//Crawl programme
	private function scrape($method, $url, $tags, $location, $month, $exclude) {
		include('methods/'.$method.'/'.$method.'.processor.php');
		return $productions;
	}
}
?>