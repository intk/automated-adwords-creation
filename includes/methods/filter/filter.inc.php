<?php
// Sanitize & trim string
function trimString($string) {

	
	//Remove tabs/unnecessary spaces and sanitize string
	$string = filter_var(trim(strip_tags(preg_replace("/\s+/", " ", $string))), FILTER_SANITIZE_STRING);
	
	if ($string == strtoupper($string)) {
		$string = ucfirst(strtolower($string));
	}

	$string = str_ireplace("&amp;", "&", $string);

	/*
	// Encode string to UTF-8
	$string = str_ireplace(array("Ã©", "Ã«", "Ã", "Ã¤", "Ó", "Ö", "情熱 - ", ' ⋅', '&#34;', 'À', '★'), array("é", "ë", "à", "ä", "O", "ö", "", "", "", "a", ""), $string);
	if ($string )
	*/
	
	//Remove curly single and double quotes from string
	
	//Replace UTF-8 special characters
	$string = str_replace(array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6", "\xc3\xa0", "\xc3\xa4", "\xc3\xa9", "\xc3\xab", "\xc3\xbc", "&#39;", '&#039;', '&#038;', '&#8211;'), array("'", "'", '"', '"', '-', '--','...', "à", "ä", "é", "ë", "ü", "'", "'", "&", "-"), $string);
	
	/*
	//Replace Windows special characters
	$string = str_replace(array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)), array("'", "'", '"', '"', '-', '--', '...'), $string);

	if (strpos($string, 't/m') !== false) {
		$output = str_replace(array("&#39;"), array("'"), $string);
	} else {
		$output = str_replace(array("&#39;", "/"), array("'","-"), $string);
	}
	*/
	return $string;

}
?>