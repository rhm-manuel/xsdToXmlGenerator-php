<?php

require_once '../lib/VulcanoXsdToXmlGenerator.php';

$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test.xsd';
$vulcanoXsdToXmlGenerator = new VulcanoXsdToXmlGenerator($file);

function getRandomValue($max = 8) {
	$asciiChars = array_merge(range(48, 57), range(97, 122));
	shuffle($asciiChars);

	return implode('', array_map('chr', array_slice($asciiChars, 0, $max, true)));
}