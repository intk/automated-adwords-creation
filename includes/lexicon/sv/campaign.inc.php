<?php
/* Swedish campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'jan.';
		$this->monthAbbr[1] = 'feb.';
		$this->monthAbbr[2] = 'mar.';
		$this->monthAbbr[3] = 'apr.';
		$this->monthAbbr[4] = 'maj.';
		$this->monthAbbr[5] = 'jun.'; 
		$this->monthAbbr[6] = 'jul.';
		$this->monthAbbr[7] = 'aug.';
		$this->monthAbbr[8] = 'sep.';
		$this->monthAbbr[9] = 'okt.';
		$this->monthAbbr[10] = 'nov.';
		$this->monthAbbr[11] = 'dec.';

		$this->monthFull[0] = 'januari';
		$this->monthFull[1] = 'februari';
		$this->monthFull[2] = 'mars';
		$this->monthFull[3] = 'april';
		$this->monthFull[4] = 'maj';
		$this->monthFull[5] = 'juni'; 
		$this->monthFull[6] = 'juli';
		$this->monthFull[7] = 'augusti';
		$this->monthFull[8] = 'september';
		$this->monthFull[9] = 'oktober';
		$this->monthFull[10] = 'november';
		$this->monthFull[11] = 'december';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teater';
		$this->keyword['tickets'] = 'biljetter';
		$this->keyword['theater'] = 'teater';
		$this->keyword['cabaret'] = 'kabaré'; 
		$this->keyword['lecture'] = 'föreläsning'; 
		$this->keyword['talk'] = 'samtal'; 
		$this->keyword['family'] = 'familj'; 
		$this->keyword['dance'] = 'dans'; 
		$this->keyword['concert'] = 'konsert';
		$this->keyword['music'] = 'musik'; 
		$this->keyword['movie'] = 'film'; 
		$this->keyword['live'] = 'live'; 
		$this->keyword['expo'] = 'utställning'; 
		$this->keyword['exhibition'] = 'utställning'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'samling'; 
		$this->keyword['art'] = 'konst'; 
		$this->keyword['circus'] = 'circus'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'dagordning';
		$this->keyword['show'] = 'föreställning';
		$this->keyword['cinema'] = 'bio';
		$this->keyword['course'] = 'kurs';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teater', 'teater', 'teater');
		$this->adPlacement['cabaret'] = array('kabaré', 'kabaré', 'kabaré');
		$this->adPlacement['musical'] = array('musikal', 'en musikal', 'musikalen');
		$this->adPlacement['dance'] = array('dans', 'dansshow', 'dansshowen');
		$this->adPlacement['movie'] = array('film', 'en film', 'filmen');
		$this->adPlacement['film'] =  array('film', 'en film', 'filmen');
		$this->adPlacement['concert'] = array('konsert', 'en konsert', 'konserten');
		$this->adPlacement['music'] = array('konsert', 'en konsert', 'konserten');
		$this->adPlacement['classic'] = array('konsert', 'en konsert', 'konserten');
		$this->adPlacement['expo'] = array('utställning', 'utställning', 'utställningen');
		$this->adPlacement['opera'] = array('opera', 'opera', 'operan');
		$this->adPlacement['show'] = array('show', 'en show', 'showen');
		$this->adPlacement['performance'] = array('föreställning', 'en föreställning', 'föreställningen');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('från', 'förbi', ' av  ', ' med ');
	}

}
?>