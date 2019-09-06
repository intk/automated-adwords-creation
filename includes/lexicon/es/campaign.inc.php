<?php
/* Spanish campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'ene';
		$this->monthAbbr[1] = 'feb';
		$this->monthAbbr[2] = 'mar';
		$this->monthAbbr[3] = 'abr';
		$this->monthAbbr[4] = 'mayo';
		$this->monthAbbr[5] = 'jun'; 
		$this->monthAbbr[6] = 'jul';
		$this->monthAbbr[7] = 'agosto';
		$this->monthAbbr[8] = 'sept';
		$this->monthAbbr[9] = 'oct';
		$this->monthAbbr[10] = 'nov';
		$this->monthAbbr[11] = 'dic';

		$this->monthFull[0] = 'enero';
		$this->monthFull[1] = 'febrero';
		$this->monthFull[2] = 'marzo';
		$this->monthFull[3] = 'abril';
		$this->monthFull[4] = 'mayo';
		$this->monthFull[5] = 'junio'; 
		$this->monthFull[6] = 'julio';
		$this->monthFull[7] = 'agosto';
		$this->monthFull[8] = 'septiembre';
		$this->monthFull[9] = 'octubre';
		$this->monthFull[10] = 'noviembre';
		$this->monthFull[11] = 'diciembre';

		// Date format
		$this->dateFormat = '%DD de %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teatro';
		$this->keyword['tickets'] = 'entradas';
		$this->keyword['theater'] = 'teatro';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'conferencia'; 
		$this->keyword['talk'] = 'talk'; 
		$this->keyword['family'] = 'família'; 
		$this->keyword['dance'] = 'danza'; 
		$this->keyword['concert'] = 'concierto';
		$this->keyword['classic'] = 'clásico';
		$this->keyword['music'] = 'música'; 
		$this->keyword['movie'] = 'película'; 
		$this->keyword['live'] = 'en vivo'; 
		$this->keyword['expo'] = 'expo'; 
		$this->keyword['exhibition'] = 'exhibition'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'colección'; 
		$this->keyword['art'] = 'art'; 
		$this->keyword['circus'] = 'circo'; 
		$this->keyword['opera'] = 'ópera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'agenda';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teatro', 'un teatro', 'el theatro');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'un musical', 'el musical');
		$this->adPlacement['dance'] = array('danza', 'un espectáculo de danza', 'el espectáculo de danza');
		$this->adPlacement['movie'] = array('película', 'una película', 'la película',);
		$this->adPlacement['concert'] = array('concierto', 'un concierto', 'el concierto');
		$this->adPlacement['music'] = array('concierto', 'un concierto', 'el concierto');
		$this->adPlacement['classic'] = array('concierto', 'un concierto', 'el concierto');
		$this->adPlacement['expo'] = array('expo', 'un expo', 'el expo');
		$this->adPlacement['opera'] = array('ópera', 'una ópera', 'la ópera');
		$this->adPlacement['show'] = array('espectáculo', 'un espectáculo', 'el espectáculo');
		$this->adPlacement['performance'] = array('espectáculo', 'un espectáculo', 'el espectáculo');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('de', 'por', 'con');
	}

}
?>