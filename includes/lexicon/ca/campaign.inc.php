<?php
/* Catalan campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'gen';
		$this->monthAbbr[1] = 'feb';
		$this->monthAbbr[2] = 'març';
		$this->monthAbbr[3] = 'abr';
		$this->monthAbbr[4] = 'maig';
		$this->monthAbbr[5] = 'jun'; 
		$this->monthAbbr[6] = 'jul';
		$this->monthAbbr[7] = 'ago';
		$this->monthAbbr[8] = 'set';
		$this->monthAbbr[9] = 'oct';
		$this->monthAbbr[10] = 'nov';
		$this->monthAbbr[11] = 'des';

		$this->monthFull[0] = 'gener';
		$this->monthFull[1] = 'febrer';
		$this->monthFull[2] = 'març';
		$this->monthFull[3] = 'abril';
		$this->monthFull[4] = 'maig';
		$this->monthFull[5] = 'juny'; 
		$this->monthFull[6] = 'juliol';
		$this->monthFull[7] = 'agost';
		$this->monthFull[8] = 'setembre';
		$this->monthFull[9] = 'octubre';
		$this->monthFull[10] = 'novembre';
		$this->monthFull[11] = 'desembre';

		// Date format
		$this->dateFormat = '%DD de %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teatre';
		$this->keyword['tickets'] = 'entrades';
		$this->keyword['theater'] = 'teatre';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'lecture';
		$this->keyword['talk'] = 'xerrades'; 
		$this->keyword['family'] = 'família'; 
		$this->keyword['dance'] = 'dansa'; 
		$this->keyword['concert'] = 'concert';
		$this->keyword['classic'] = 'clàssica';
		$this->keyword['music'] = 'música'; 
		$this->keyword['movie'] = 'pellícula'; 
		$this->keyword['live'] = 'en viu'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'exhibition'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'collecció'; 
		$this->keyword['art'] = 'art'; 
		$this->keyword['circus'] = 'circ'; 
		$this->keyword['opera'] = 'òpera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'agenda';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teatre', 'teatre', 'teatre');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'un musical', 'el musical');
		$this->adPlacement['dance'] = array('dansa', 'un espectacle de dansa', 'l\'espectacle de dansa');
		$this->adPlacement['movie'] = array('pellícula', 'un pellícula', 'la pellícula',);
		$this->adPlacement['concert'] = array('concert', 'un concert', 'el concert');
		$this->adPlacement['music'] = array('concert', 'un concert', 'el concert');
		$this->adPlacement['classic'] = array('concert', 'un concert', 'el concert');
		$this->adPlacement['expo'] = array('expo', 'una expo', 'l\'exposició');
		$this->adPlacement['opera'] = array('òpera', 'òpera', 'l\'òpera');
		$this->adPlacement['show'] = array('espectacle', 'un espectacle', 'l\'espectacle');
		$this->adPlacement['performance'] = array('espectacle', 'un espectacle', 'l\'espectacle');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('de', 'per', 'amb');
	}

}
?>