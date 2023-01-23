<?php
/* Finnish campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'tammikuuta';
		$this->monthAbbr[1] = 'helmikuuta';
		$this->monthAbbr[2] = 'maaliskuuta';
		$this->monthAbbr[3] = 'huhtikuuta';
		$this->monthAbbr[4] = 'toukokuuta';
		$this->monthAbbr[5] = 'kesäkuuta'; 
		$this->monthAbbr[6] = 'heinäkuuta';
		$this->monthAbbr[7] = 'elokuuta';
		$this->monthAbbr[8] = 'syyskuuta';
		$this->monthAbbr[9] = 'lokakuuta';
		$this->monthAbbr[10] = 'marraskuuta';
		$this->monthAbbr[11] = 'joulukuuta';

		$this->monthFull[0] = 'tammikuuta';
		$this->monthFull[1] = 'helmikuuta';
		$this->monthFull[2] = 'maaliskuuta';
		$this->monthFull[3] = 'huhtikuuta';
		$this->monthFull[4] = 'toukokuuta';
		$this->monthFull[5] = 'kesäkuuta'; 
		$this->monthFull[6] = 'heinäkuuta';
		$this->monthFull[7] = 'elokuuta';
		$this->monthFull[8] = 'syyskuuta';
		$this->monthFull[9] = 'lokakuuta';
		$this->monthFull[10] = 'marraskuuta';
		$this->monthFull[11] = 'joulukuuta';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teatteri';
		$this->keyword['tickets'] = 'liput';
		$this->keyword['theater'] = 'teatteri';
		$this->keyword['cabaret'] = 'kabaree'; 
		$this->keyword['lecture'] = 'luento'; 
		$this->keyword['talk'] = 'puhua'; 
		$this->keyword['family'] = 'perhe'; 
		$this->keyword['dance'] = 'tanssi'; 
		$this->keyword['concert'] = 'konsertti';
		$this->keyword['classic'] = 'klassikko';
		$this->keyword['music'] = 'musiikkia'; 
		$this->keyword['movie'] = 'elokuva'; 
		$this->keyword['screening'] = 'seulonta';
		$this->keyword['live'] = 'suorana'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'näyttely'; 
		$this->keyword['festival'] = 'festivaali'; 
		$this->keyword['collection'] = 'kokoelma'; 
		$this->keyword['art'] = 'taide'; 
		$this->keyword['circus'] = 'sirkus'; 
		$this->keyword['opera'] = 'ooppera'; 
		$this->keyword['online'] = 'suora';
		$this->keyword['agenda'] = 'asialista';
		$this->keyword['workshop'] = 'työpaja';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teatteri', 'teatteri', 'teatteri');
		$this->adPlacement['cabaret'] = array('kabaree', 'kabaree', 'kabaree');
		$this->adPlacement['musical'] = array('musikaali', 'musikaali', 'musikaali');
		$this->adPlacement['dance'] = array('tanssi', 'tanssi', 'tanssi');
		$this->adPlacement['movie'] = array('elokuva', 'elokuva', 'elokuva',);
		$this->adPlacement['film'] = array('elokuva', 'elokuva', 'elokuva');
		$this->adPlacement['screening'] = array('seulonta', 'seulonta', 'seulonta');
		$this->adPlacement['concert'] = array('konsertti', 'konsertti', 'konsertti');
		$this->adPlacement['music'] = array('musiikkia', 'musiikkia', 'musiikkia');
		$this->adPlacement['classic'] = array('konsertti', 'konsertti', 'konsertti');
		$this->adPlacement['expo'] = array('expo', 'expo', 'expo');
		$this->adPlacement['opera'] = array('ooppera', 'ooppera', 'ooppera');
		$this->adPlacement['show'] = array('näytä', 'näytä', 'näytä');
		$this->adPlacement['workshop'] = array('työpaja', 'työpaja', 'työpaja');
		$this->adPlacement['tour'] = array('kiertue', 'kiertue', 'kiertue');
		$this->adPlacement['performance'] = array('esitys', 'esitys', 'esitys');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('Ohjaaja:', 'by', 'with');
	}

}
?>