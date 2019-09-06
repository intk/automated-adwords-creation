<?php
/* English campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'Jan';
		$this->monthAbbr[1] = 'Feb';
		$this->monthAbbr[2] = 'Mar';
		$this->monthAbbr[3] = 'Apr';
		$this->monthAbbr[4] = 'May';
		$this->monthAbbr[5] = 'Jun'; 
		$this->monthAbbr[6] = 'Jul';
		$this->monthAbbr[7] = 'Aug';
		$this->monthAbbr[8] = 'Sep';
		$this->monthAbbr[9] = 'Oct';
		$this->monthAbbr[10] = 'Nov';
		$this->monthAbbr[11] = 'Dec';

		$this->monthFull[0] = 'January';
		$this->monthFull[1] = 'February';
		$this->monthFull[2] = 'March';
		$this->monthFull[3] = 'April';
		$this->monthFull[4] = 'May';
		$this->monthFull[5] = 'Juni'; 
		$this->monthFull[6] = 'July';
		$this->monthFull[7] = 'August';
		$this->monthFull[8] = 'September';
		$this->monthFull[9] = 'October';
		$this->monthFull[10] = 'November';
		$this->monthFull[11] = 'December';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'theatre';
		$this->keyword['tickets'] = 'tickets';
		$this->keyword['theater'] = 'theater';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'lecture'; 
		$this->keyword['talk'] = 'talk'; 
		$this->keyword['family'] = 'family'; 
		$this->keyword['dance'] = 'dance'; 
		$this->keyword['concert'] = 'concert';
		$this->keyword['classic'] = 'classic';
		$this->keyword['music'] = 'music'; 
		$this->keyword['movie'] = 'movie'; 
		$this->keyword['live'] = 'live'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'exhibition'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'collection'; 
		$this->keyword['art'] = 'art'; 
		$this->keyword['circus'] = 'circus'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'agenda';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('theatre', 'theatre', 'theatre');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'a musical', 'the musical');
		$this->adPlacement['dance'] = array('dance', 'a dance', 'the dance');
		$this->adPlacement['movie'] = array('movie', 'the movie', 'the movie',);
		$this->adPlacement['concert'] = array('concert', 'a concert', 'the concert');
		$this->adPlacement['music'] = array('concert', 'a concert', 'the concert');
		$this->adPlacement['classic'] = array('concert', 'a concert', 'the concert');
		$this->adPlacement['expo'] = array('expo', 'the expo', 'the expo');
		$this->adPlacement['opera'] = array('opera', 'opera', 'the opera');
		$this->adPlacement['show'] = array('show', 'a show', 'the show');
		$this->adPlacement['performance'] = array('performance', 'a performance', 'the performance');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('from', 'by', 'with');
	}

}
?>