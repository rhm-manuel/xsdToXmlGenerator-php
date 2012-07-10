<?php

require_once '../lib/VulcanoXsdToXmlGenerator.php';

$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test.xsd';
$vulcanoXsdToXmlGenerator = new VulcanoXsdToXmlGenerator($file);

function getRandomValue() {
	$max		= 8;
	$string		= '';
	$asciiChars	= array_merge(range(48, 57), range(97, 122));

	for ($i=0; $i < $max; ++$i) {
		shuffle($asciiChars);
		$random = $asciiChars;
		$string .= chr(reset($random));
	}

	return $string;
}