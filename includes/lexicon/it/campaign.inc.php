<?php
/* English campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'Gen.';
		$this->monthAbbr[1] = 'Feb.';
		$this->monthAbbr[2] = 'Mar.';
		$this->monthAbbr[3] = 'Apr.';
		$this->monthAbbr[4] = 'Mag.';
		$this->monthAbbr[5] = 'Giu.'; 
		$this->monthAbbr[6] = 'Lug.';
		$this->monthAbbr[7] = 'Ago.';
		$this->monthAbbr[8] = 'Sett.';
		$this->monthAbbr[9] = 'Ott.';
		$this->monthAbbr[10] = 'Nov.';
		$this->monthAbbr[11] = 'Dic.';

		$this->monthFull[0] = 'Gennaio';
		$this->monthFull[1] = 'Febbraio';
		$this->monthFull[2] = 'Marzo';
		$this->monthFull[3] = 'Aprile';
		$this->monthFull[4] = 'Maggio';
		$this->monthFull[5] = 'Giugno'; 
		$this->monthFull[6] = 'Luglio';
		$this->monthFull[7] = 'Agosto';
		$this->monthFull[8] = 'Settembre';
		$this->monthFull[9] = 'Ottobre';
		$this->monthFull[10] = 'Novembre';
		$this->monthFull[11] = 'Dicembre';

		// Date format
		$this->dateFormat = '%DD %MM';

		// Keyword suffixes
		$this->keyword['theatre'] = 'teatro';
		$this->keyword['tickets'] = 'ticket';
		$this->keyword['theater'] = 'teatrale';
		$this->keyword['cabaret'] = 'cabaret'; 
		$this->keyword['lecture'] = 'conferenza'; 
		$this->keyword['talk'] = 'discorso'; 
		$this->keyword['family'] = 'famiglia'; 
		$this->keyword['dance'] = 'danza'; 
		$this->keyword['concert'] = 'concerto';
		$this->keyword['classic'] = 'classica';
		$this->keyword['music'] = 'musica'; 
		$this->keyword['movie'] = 'movie'; 
		$this->keyword['live'] = 'live'; 
		$this->keyword['expo'] = 'esposizione'; 
		$this->keyword['exhibition'] = 'mostra'; 
		$this->keyword['festival'] = 'festival'; 
		$this->keyword['collection'] = 'collezione'; 
		$this->keyword['art'] = 'arte'; 
		$this->keyword['circus'] = 'circo'; 
		$this->keyword['opera'] = 'opera'; 
		$this->keyword['online'] = 'online';
		$this->keyword['agenda'] = 'agenda';
		$this->keyword['workshop'] = 'officina';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('teatro', 'teatro', 'teatro');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'un musical', 'il musical');
		$this->adPlacement['dance'] = array('danza', 'uno spettacolo di danza', 'lo spettacolo di danza');
		$this->adPlacement['movie'] = array('film', 'un film', 'il film',);
		$this->adPlacement['film'] = array('film', 'un film', 'il film');
		$this->adPlacement['concert'] = array('concerto', 'un concerto', 'il concerto');
		$this->adPlacement['music'] = array('concerto', 'un concerto', 'il concerto');
		$this->adPlacement['classic'] = array('concerto', 'un concerto', 'il concerto');
		$this->adPlacement['expo'] = array('esposizione', 'esposizione', 'l\'esposizione');
		$this->adPlacement['opera'] = array('opera', 'opera', 'l\'opera');
		$this->adPlacement['show'] = array('spettacolo', 'a show', 'the show');
		$this->adPlacement['performance'] = array('spettacolo', 'uno spettacolo', 'lo spettacolo');

		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('da ', 'da ', 'con ');
	}

}
?>