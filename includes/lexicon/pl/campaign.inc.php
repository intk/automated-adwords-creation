<?php
/* Polish campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'Sty.';
		$this->monthAbbr[1] = 'Lut.';
		$this->monthAbbr[2] = 'Mar.';
		$this->monthAbbr[3] = 'Kwi.';
		$this->monthAbbr[4] = 'Maj.';
		$this->monthAbbr[5] = 'Cze.'; 
		$this->monthAbbr[6] = 'Lip.';
		$this->monthAbbr[7] = 'Sie.';
		$this->monthAbbr[8] = 'Wrz.';
		$this->monthAbbr[9] = 'Paź.';
		$this->monthAbbr[10] = 'Lis.';
		$this->monthAbbr[11] = 'Gru.';

		$this->monthFull[0] = 'stycznia';
		$this->monthFull[1] = 'lutego';
		$this->monthFull[2] = 'marca';
		$this->monthFull[3] = 'kwietnia';
		$this->monthFull[4] = 'maja';
		$this->monthFull[5] = 'czerwca'; 
		$this->monthFull[6] = 'lipca';
		$this->monthFull[7] = 'sierpnia';
		$this->monthFull[8] = 'września';
		$this->monthFull[9] = 'października';
		$this->monthFull[10] = 'listopada';
		$this->monthFull[11] = 'grudnia';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teatr';
		$this->keyword['tickets'] = 'bilety';
		$this->keyword['theater'] = 'teatr';
		$this->keyword['cabaret'] = 'kabaret'; 
		$this->keyword['lecture'] = 'wykład'; 
		$this->keyword['talk'] = 'rozmowa'; 
		$this->keyword['family'] = 'rodzina'; 
		$this->keyword['dance'] = 'taniec'; 
		$this->keyword['concert'] = 'koncert';
		$this->keyword['classic'] = 'klasyczny';
		$this->keyword['music'] = 'muzyka'; 
		$this->keyword['movie'] = 'film'; 
		$this->keyword['live'] = 'na żywo'; 
		$this->keyword['expo'] = 'wystawa'; 
		$this->keyword['exhibition'] = 'wystawa'; 
		$this->keyword['festival'] = 'festiwal'; 
		$this->keyword['collection'] = 'kolekcja'; 
		$this->keyword['art'] = 'sztuka'; 
		$this->keyword['circus'] = 'sztuka'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'program';
		$this->keyword['comedy'] = 'komedia';
		$this->keyword['drama'] = 'dramat';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teatr', 'teatr', 'teatr');
		$this->adPlacement['cabaret'] = array('kabaret', 'kabaret', 'kabaret');
		$this->adPlacement['musical'] = array('musical', 'musical', 'musical');
		$this->adPlacement['dance'] = array('taniec', 'taniec', 'taniec');
		$this->adPlacement['movie'] = array('film', 'film', 'film',);
		$this->adPlacement['film'] = array('film', 'film', 'film');
		$this->adPlacement['concert'] = array('concert', 'a concert', 'the concert');
		$this->adPlacement['music'] = array('koncert', 'koncert', 'koncert');
		$this->adPlacement['expo'] = array('wystawa', 'wystawa', 'wystawa');
		$this->adPlacement['opera'] = array('opera', 'opera', 'opera');
		$this->adPlacement['show'] = array('przedstawienie', 'przedstawienie', 'przedstawienie');
		$this->adPlacement['performance'] = array('przedstawienie', 'przedstawienie', 'przedstawienie');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array(' od ', 'przez', ' z ') ;
	}

}
?>