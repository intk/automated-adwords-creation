<?php
/* Dutch campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'jan.';
		$this->monthAbbr[1] = 'feb.';
		$this->monthAbbr[2] = 'mar.';
		$this->monthAbbr[3] = 'apr.';
		$this->monthAbbr[4] = 'maj';
		$this->monthAbbr[5] = 'jun.'; 
		$this->monthAbbr[6] = 'jul.';
		$this->monthAbbr[7] = 'avg.';
		$this->monthAbbr[8] = 'sept.';
		$this->monthAbbr[9] = 'okt.';
		$this->monthAbbr[10] = 'nov.';
		$this->monthAbbr[11] = 'dec.';

		$this->monthFull[0] = 'januar';
		$this->monthFull[1] = 'februar';
		$this->monthFull[2] = 'marec';
		$this->monthFull[3] = 'april';
		$this->monthFull[4] = 'maj';
		$this->monthFull[5] = 'junij'; 
		$this->monthFull[6] = 'julij';
		$this->monthFull[7] = 'avgust';
		$this->monthFull[8] = 'september';
		$this->monthFull[9] = 'oktober';
		$this->monthFull[10] = 'november';
		$this->monthFull[11] = 'december';

		// Date format
		$this->dateFormat = '%DD. %MM';
		$this->dateFormatShort = '%DD. %m. %Y';

		// Keyword suffixes
		$this->keyword['theatre'] = 'gledališče';
		$this->keyword['tickets'] = 'vstopnice';
		$this->keyword['theater'] = 'teater';
		$this->keyword['cabaret'] = 'kabaret'; 
		$this->keyword['lecture'] = 'predavanje'; 
		$this->keyword['talk'] = 'pogovor'; 
		$this->keyword['workshop'] = 'delavnice';
		$this->keyword['family'] = 'družina'; 
		$this->keyword['dance'] = 'ples'; 
		$this->keyword['concert'] = 'koncert';
		$this->keyword['classic'] = 'klasika';
		$this->keyword['music'] = 'glasba'; 
		$this->keyword['movie'] = 'film'; 
		$this->keyword['live'] = 'v živo'; 
		$this->keyword['expo'] = 'razstava'; 
		$this->keyword['visualArts'] = 'vizualna umetnost';
		$this->keyword['exhibition'] = 'razstava'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'zbirka'; 
		$this->keyword['art'] = 'umetnost'; 
		$this->keyword['circus'] = 'cirkus'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'na spletu';
		$this->keyword['agenda'] = 'dnevni red';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('gledališče', 'gledališče', 'gledališče');
		$this->adPlacement['cabaret'] = array('kabaret', 'kabaret', 'kabaret');
		$this->adPlacement['musical'] = array('mjuzikel', 'mjuzikel', 'mjuzikel');
		$this->adPlacement['lecture'] = array('predavanje', 'predavanje', 'predavanje');
		$this->adPlacement['dance'] = array('ples', 'plesna predstava', 'plesna predstava');
		$this->adPlacement['movie'] = array('film', 'film', 'film',);
		$this->adPlacement['concert'] = array('koncert', 'koncert', 'koncert');
		$this->adPlacement['music'] = array('glasba', 'koncert', 'koncert');
		$this->adPlacement['classic'] = array('klasika', 'koncert', 'koncert');
		$this->adPlacement['expo'] = array('razstava', 'razstava', 'razstava');
		$this->adPlacement['visualArts'] = array('vizualna umetnost', 'vizualna umetnost', 'vizualna umetnost');
		$this->adPlacement['opera'] = array('opera', 'opera', 'opera');
		$this->adPlacement['show'] = array('dogodek', 'dogodek', 'dogodek');
		$this->adPlacement['workshop'] = array('delavnice', 'delavnice', 'delavnice');
		$this->adPlacement['performance'] = array('predstava', 'predstava', 'predstava');


		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('iz', 'z', 's');
	}

}
?>