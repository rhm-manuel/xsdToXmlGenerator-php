<?php

require_once 'bootstrap.php';

/**
 * @var $vulcanoXsdToXmlGenerator VulcanoXsdToXmlGenerator
 */

// as string
echo $vulcanoXsdToXmlGenerator->getRequiredData();

// as array
$array = $vulcanoXsdToXmlGenerator->getRequiredData(false);
print_r($array);