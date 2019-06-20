<?php
/* Campaign Dutch lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'jan';
		$this->monthAbbr[1] = 'feb';
		$this->monthAbbr[2] = 'mrt';
		$this->monthAbbr[3] = 'apr';
		$this->monthAbbr[4] = 'mei';
		$this->monthAbbr[5] = 'jun'; 
		$this->monthAbbr[6] = 'jul';
		$this->monthAbbr[7] = 'aug';
		$this->monthAbbr[8] = 'sep';
		$this->monthAbbr[9] = 'okt';
		$this->monthAbbr[10] = 'nov';
		$this->monthAbbr[11] = 'dec';

		$this->monthFull[0] = 'januari';
		$this->monthFull[1] = 'februari';
		$this->monthFull[2] = 'maart';
		$this->monthFull[3] = 'april';
		$this->monthFull[4] = 'mei';
		$this->monthFull[5] = 'juni'; 
		$this->monthFull[6] = 'juli';
		$this->monthFull[7] = 'augustus';
		$this->monthFull[8] = 'september';
		$this->monthFull[9] = 'oktober';
		$this->monthFull[10] = 'november';
		$this->monthFull[11] = 'december';

		// Keyword suffixes
		$this->keyword['theatre'] = 'toneel';
		$this->keyword['tickets'] = 'tickets';
		$this->keyword['theater'] = 'theater';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'lezing'; 
		$this->keyword['talk'] = 'talk'; 
		$this->keyword['family'] = 'familie'; 
		$this->keyword['dance'] = 'dans'; 
		$this->keyword['concert'] = 'concert';
		$this->keyword['classic'] = 'klassiek';
		$this->keyword['music'] = 'muziek'; 
		$this->keyword['movie'] = 'film'; 
		$this->keyword['live'] = 'live'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'tentoonstelling'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'collectie'; 
		$this->keyword['art'] = 'kunst'; 
		$this->keyword['circus'] = 'circus'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'agenda';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('toneel', 'toneel', 'toneel');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'een musical', 'de musical');
		$this->adPlacement['dance'] = array('dans', 'een danssshow', 'de danssshow');
		$this->adPlacement['movie'] = array('film', 'de film', 'de film',);
		$this->adPlacement['concert'] = array('concert', 'een concert', 'het concert');
		$this->adPlacement['classic'] = array('concert', 'een concert', 'het concert');
		$this->adPlacement['expo'] = array('expo', 'de expo', 'de expo');
		$this->adPlacement['opera'] = array('opera', 'opera', 'de opera');
		$this->adPlacement['show'] = array('show', 'een show', 'de show');
		$this->adPlacement['performance'] = array('voorstelling', 'een voorstelling', 'de voorstelling');
	}

}
?>