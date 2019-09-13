<?php
/* French campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'janv.';
		$this->monthAbbr[1] = 'févr.';
		$this->monthAbbr[2] = 'mars';
		$this->monthAbbr[3] = 'avr.';
		$this->monthAbbr[4] = 'mai.';
		$this->monthAbbr[5] = 'juin'; 
		$this->monthAbbr[6] = 'juil.';
		$this->monthAbbr[7] = 'août';
		$this->monthAbbr[8] = 'sept.';
		$this->monthAbbr[9] = 'oct.';
		$this->monthAbbr[10] = 'nov.';
		$this->monthAbbr[11] = 'déc.';

		$this->monthFull[0] = 'janvier';
		$this->monthFull[1] = 'février';
		$this->monthFull[2] = 'mars';
		$this->monthFull[3] = 'avril';
		$this->monthFull[4] = 'mai';
		$this->monthFull[5] = 'juin'; 
		$this->monthFull[6] = 'juillet';
		$this->monthFull[7] = 'août';
		$this->monthFull[8] = 'septembre';
		$this->monthFull[9] = 'octobre';
		$this->monthFull[10] = 'novembre';
		$this->monthFull[11] = 'décembre';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'théâtre';
		$this->keyword['tickets'] = 'tickets';
		$this->keyword['theater'] = 'théâtre';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'conférence'; 
		$this->keyword['family'] = 'famille'; 
		$this->keyword['dance'] = 'danse'; 
		$this->keyword['concert'] = 'concert';
		$this->keyword['classic'] = 'classique';
		$this->keyword['music'] = 'musique'; 
		$this->keyword['movie'] = 'film'; 
		$this->keyword['live'] = 'vivre'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'exposition'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'collection'; 
		$this->keyword['art'] = 'art'; 
		$this->keyword['circus'] = 'cirque'; 
		$this->keyword['opera'] = 'opéra'; 
		$this->keyword['online'] = 'en ligne';
		$this->keyword['agenda'] = 'programme';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('théâtre', 'théâtre', 'théâtre');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musicale', 'une comédie musicale', 'la comédie musicale');
		$this->adPlacement['dance'] = array('danse', 'un danse', 'le danse');
		$this->adPlacement['movie'] = array('film', 'un film', 'le film',);
		$this->adPlacement['concert'] = array('concert', 'un concert', 'le concert');
		$this->adPlacement['music'] = array('concert', 'un concert', 'le concert');
		$this->adPlacement['classic'] = array('concert', 'un concert', 'le concert');
		$this->adPlacement['expo'] = array('expo', 'une expo', 'l\'expo');
		$this->adPlacement['opera'] = array('opéra', 'opéra', 'l\'opéra');
		$this->adPlacement['show'] = array('spectacle', 'un spectacle', 'le spectacle');
		$this->adPlacement['performance'] = array('performance', 'une performance', 'la performance');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('de', 'par', 'avec');
	}

}
?>