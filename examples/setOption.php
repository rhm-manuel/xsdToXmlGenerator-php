<?php

require_once 'bootstrap.php';

/**
 * @var $vulcanoXsdToXmlGenerator VulcanoXsdToXmlGenerator
 */

/**
 * Throws an exception! Because some data is missing!
 */

$vulcanoXsdToXmlGenerator->setOption(VulcanoXsdToXmlGenerator::OPTION_HOLD_ON_MISSING_DATA_NODE_ATTRIBUTE);
$vulcanoXsdToXmlGenerator->markNodeAsOptional('vehicle');

for ($loop = 0; $loop < 5; ++$loop) {
	$vulcanoXsdToXmlGenerator->appendNodeData('donkey', array(
		'name' => getRandomValue(),
		'birthday' => getRandomValue(),
	));

	$vulcanoXsdToXmlGenerator->appendNodeData('cow', array(
		'name' => getRandomValue(),
		'birthday' => getRandomValue(),
	));

	$vulcanoXsdToXmlGenerator->appendNodeData('pic', array(
		'name' => getRandomValue(),
		'birthday' => getRandomValue(),
	));
}

echo $vulcanoXsdToXmlGenerator->generate();