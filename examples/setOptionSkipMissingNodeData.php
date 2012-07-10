<?php

require_once 'bootstrap.php';

/**
 * @var $vulcanoXsdToXmlGenerator VulcanoXsdToXmlGenerator
 */

/**
 * Throws an exception! Because some data is missing!
 */

$vulcanoXsdToXmlGenerator->setOption(VulcanoXsdToXmlGenerator::OPTION_SKIP_MISSING_NODE_DATA);

$vulcanoXsdToXmlGenerator->appendNodeData('farm', array());
$vulcanoXsdToXmlGenerator->appendNodeData('vehicle', array());
$vulcanoXsdToXmlGenerator->appendNodeData('tractor', array('name' => 'bob'));
$vulcanoXsdToXmlGenerator->appendNodeData('tractor', array('name' => 'jonny'));

echo $vulcanoXsdToXmlGenerator->generate();