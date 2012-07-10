<?php

require_once 'bootstrap.php';

/**
 * @var $vulcanoXsdToXmlGenerator VulcanoXsdToXmlGenerator
 */

$vulcanoXsdToXmlGenerator->appendNodeData( 'atom', array(
														'name' => getRandomValue(),
												   ) );

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

$vulcanoXsdToXmlGenerator->appendNodeData( 'corn', array(
														'number' => getRandomValue(),
												   ) );

$vulcanoXsdToXmlGenerator->appendNodeData( 'farm', array(
														'name' => getRandomValue(),
														'size' => getRandomValue(),
												   ) );

$vulcanoXsdToXmlGenerator->appendNodeData( 'created', array(
														   'date' => getRandomValue(),
													  ) );

$vulcanoXsdToXmlGenerator->appendNodeData( 'tractor', array(
														   'name' => getRandomValue(),
													  ) );


echo $vulcanoXsdToXmlGenerator->generate();