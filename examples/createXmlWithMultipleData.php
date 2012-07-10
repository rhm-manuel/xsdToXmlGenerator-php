<?php

require_once 'bootstrap.php';

/**
 * @var $vulcanoXsdToXmlGenerator VulcanoXsdToXmlGenerator
 */

for ($loop = 0; $loop < 5; ++$loop) {
	$vulcanoXsdToXmlGenerator->appendNodeData( 'donkey', array(
														  'name' => getRandomValue(),
														  'birthday' => getRandomValue(),
													 ) );

	$vulcanoXsdToXmlGenerator->appendNodeData( 'cow', array(
														   'name' => getRandomValue(),
														   'birthday' => getRandomValue(),
													  ) );

	$vulcanoXsdToXmlGenerator->appendNodeData( 'pic', array(
														   'name' => getRandomValue(),
														   'birthday' => getRandomValue(),
													  ) );
}

echo $vulcanoXsdToXmlGenerator->generate();