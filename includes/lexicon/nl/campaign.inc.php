<?php
/* Dutch campaign lexicon */

class Lexicon {
	public function __construct() {

		// Define months
		$this->monthAbbr[0] = 'jan.';
		$this->monthAbbr[1] = 'feb.';
		$this->monthAbbr[2] = 'mrt.';
		$this->monthAbbr[3] = 'apr.';
		$this->monthAbbr[4] = 'mei';
		$this->monthAbbr[5] = 'jun.'; 
		$this->monthAbbr[6] = 'jul.';
		$this->monthAbbr[7] = 'aug.';
		$this->monthAbbr[8] = 'sep.';
		$this->monthAbbr[9] = 'okt.';
		$this->monthAbbr[10] = 'nov.';
		$this->monthAbbr[11] = 'dec.';

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

		// Date format
		$this->dateFormat = '%DD %MM';

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
		$this->keyword['screening'] = 'screening'; 
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
		$this->keyword['workshop'] = 'workshop';
		$this->keyword['uitstap'] = 'uitstap';
		$this->keyword['vorming'] = 'vorming';


		// Genre placements for the ads
		$this->adPlacement['theatre'] = array('toneel', 'toneel', 'toneel');
		$this->adPlacement['cabaret'] = array('cabaret', 'cabaret', 'cabaret');
		$this->adPlacement['musical'] = array('musical', 'een musical', 'de musical');
		$this->adPlacement['dance'] = array('dans', 'een dansshow', 'de dansshow');
		$this->adPlacement['movie'] = array('film', 'een film', 'de film');
		$this->adPlacement['screening'] = array('screening', 'a filmvertoning', 'de filmvertoning');
		$this->adPlacement['concert'] = array('concert', 'een concert', 'het concert');
		$this->adPlacement['music'] = array('muziek', 'een concert', 'het concert');
		$this->adPlacement['classic'] = array('concert', 'een concert', 'het concert');
		$this->adPlacement['expo'] = array('expo', 'de expo', 'de expo');
		$this->adPlacement['opera'] = array('opera', 'opera', 'de opera');
		$this->adPlacement['show'] = array('show', 'een show', 'de show');
		$this->adPlacement['workshop'] = array('workshop', 'een workshop', 'de workshop');
		$this->adPlacement['uitstap'] = array('uitstap', 'een activiteit', 'de activiteit');
		$this->adPlacement['vorming'] = array('vorming', 'een cursus', 'de cursus');
		$this->adPlacement['performance'] = array('voorstelling', 'een voorstelling', 'de voorstelling');


		// Prepositions to remove from end or beginning of ad group name
		$this->prepositions = array('van', 'door', 'Door ', 'Met o.a. ', ' met ', 'o.l.v.', 'olv', 'e.a.', 'o.a.', 'e.v.a.', 'i.s.m.', 'Regisseur:', 'Acteurs:', '(Stem)' , '(Zichzelf)', 'try-out', '(try-out)', '(reprise)', 'première', '(première)', 'Film:', 'support', 'sopraan', 'alt', 'tenor', 'piano', 'tenorsaxofoon', 'altsaxofoon', 'saxofoon', 'klarinet', 'trombone', 'trompet', 'dirigent', 'organ', 'componist', 'producer', 'harp', 'contrabas', 'bas', 'altviool', 'viool',  'zang', 'basgitaar', 'gitaar', 'spoken word', 'fluit', 'cello', 'drums', 'percussie', 'harp', 'hobo', 'hoorn', 'DJ');
	}

}
?>