<?php

/**
 * This class catapulted volcanic xml to the surface. ;)
 * This class generates xml markup by xsd file.
 * Feel free to append your custom-data by node-namespace.
 * Your assigned data should represent the attributes.
 * FYI: You have to create a xsd file from xml.
 * Use can use http://www.dotkam.com/2008/05/28/generate-xsd-from-xml/ as converter
 * until we implement our own converter! Or you
 * Design-type: Local elements / global complex types
 * Detect simple content type: Smart
 *
 * @author        redhotmagma        info[at]redhotmagma[dot]de        (http://www.redhotmagma.de)
 * @version        0.1
 * @example

$vulcanoXsdToXmlGenerator = new VulcanoXsdToXmlGenerator($pathToXsdFile);
 * $nodeData = array(
 * 'attributeName1' => 'attributeValue1',
 * 'attributeName2' => 'attributeValue2',
 * 'attributeName3' => 'attributeValue3',
 * );
 * $vulcanoXsdToXmlGenerator->appendNodeData('nodeName', $nodeData);
 * //$vulcanoXsdToXmlGenerator->setOption(VulcanoXsdToXmlGenerator::OPTION_HOLD_ON_MISSING_DATA_NODE_ATTRIBUTE);
 * $xml = $vulcanoXsdToXmlGenerator->generate();
 * var_dump($xml);


 */
class VulcanoXsdToXmlGenerator {

	/**
	 * Behavior for missing data attributes by node name.
	 */
	const OPTION_HOLD_ON_MISSING_DATA_NODE_ATTRIBUTE = 1;

	/**
	 * Behavior for empty attribute value.
	 */
	const OPTION_SHOW_MISSING_NODE_ATTRIBUTES_AS_EMPTY = 2;

	/**
	 * Behavior for missing nodes.
	 */
	const OPTION_SKIP_MISSING_NODE_DATA = 4;

	/**
	 * Default value for empty attribute values.
	 */
	const EMPTY_CELL_VALUE = '?';

	/**
	 * @var int
	 */
	private $options = self::OPTION_SHOW_MISSING_NODE_ATTRIBUTES_AS_EMPTY;

	/**
	 * Path to xsd file.
	 *
	 * @var string
	 */
	private $xsdFile;

	/**
	 * Contains the content of xsd file.
	 *
	 * @var string
	 */
	private $xsdSource;

	/**
	 * Contains the structure of xml.
	 *
	 * @var array
	 */
	private $xmlStructure = array();

	/**
	 * Contains all attributes of nodes.
	 *
	 * @var array
	 */
	private $nodeDefinitionAttributes = array();

	/**
	 * Contains the custom assigned data.
	 * Each node can be contain multiple data and
	 * your data will shown as attributes in a node.
	 *
	 * @var array
	 */
	private $assignedData = array();

	/**
	 * @var \DOMDocument
	 */
	private $domDocument;

	/**
	 * XML Iteration reference.
	 *
	 * @var array
	 */
	private $xmlReference = array();

	/**
	 * Contains optional marked nodes. They will not displayed on missing assigned data.
	 *
	 * @var array
	 */
	private $optionalNodes = array();

	/**
	 * @param    string $xsdFile path o file
	 *
	 * @throws \Exception
	 */
	public function __construct($xsdFile) {

		$this->xsdFile = $xsdFile;

		if (!is_readable($this->xsdFile)) {
			throw new \Exception('This file does not exists or is not readable.');
		}

		$this->xsdSource = file_get_contents($xsdFile);
		$this->domDocument = new \DOMDocument('1.0', "UTF-8");
		$this->domDocument->formatOutput = true;
		$this->domDocument->preserveWhiteSpace = false;
	}

	/**
	 * Sets bit mask by flag.
	 *
	 * @param    int $option
	 */
	public function setOption($option) {
		if (!($this->isOptionIsEnabled($option))) {
			$this->options ^= $option;
		}
	}

	/**
	 * Unset bit mask by flag.
	 *
	 * @param    int $option
	 */
	public function unsetOption($option) {
		if ($this->isOptionIsEnabled($option)) {
			$this->options ^= $option;
		}
	}

	/**
	 * Checks whether the given flag is set.
	 *
	 * @param    int $option
	 *
	 * @return    bool
	 */
	private function isOptionIsEnabled($option) {
		return (bool)($this->options & $option);
	}

	/**
	 * Returns xml markup as string.
	 *
	 * @return string
	 */
	public function generate() {
		$this->parseStructure();
		$this->processGenerateXML($this->xmlStructure);

		// reload XML to cause format output and/or preserve whitespace to take effect.
		$this->domDocument->loadXML($this->domDocument->saveXML());

		return $this->domDocument->saveXML();
	}

	/**
	 * @param $structure
	 */
	private function processGenerateXML($structure) {
		foreach ($structure as $rootNode => $childNode) {
			$nodes = $this->createNode($rootNode, $childNode);
			foreach ($nodes as $node) {
				$this->domDocument->appendChild($node);
			}
		}
	}

	/**
	 * Returns the assigned data of node.
	 *
	 * @param    string $nodeName
	 *
	 * @return array
	 */
	private function getAssignedDataByNode($nodeName) {
		$data = array();
		if (!empty($this->assignedData[$nodeName])) {
			$data = $this->assignedData[$nodeName];
		}

		return $data;
	}

	/**
	 * Set the as optional.
	 *
	 * @param    string $nodeName
	 */
	public function markNodeAsOptional($nodeName) {
		$this->optionalNodes[$nodeName] = $nodeName;
	}

	/**
	 * Returns whether the node is marked as optional.
	 *
	 * @param    string $nodeName
	 *
	 * @return bool
	 */
	private function isNodeMarkedAsOptional($nodeName) {
		return (bool)isset($this->optionalNodes[$nodeName]);
	}

	/**
	 * The heart of this class.
	 * Recursive node generation under consideration of the individual assigned data.
	 *
	 * @param    string $rootNodeName
	 * @param    array $childNodes
	 *
	 * @throws \Exception
	 * @return array|DOMDocument[]
	 */
	private function createNode($rootNodeName, $childNodes) {
		$appendedData = $this->getAssignedDataByNode($rootNodeName);
		$appendedDataCount = count($appendedData);

		if (empty($appendedDataCount)) {
			$appendedDataCount = 1;

			$option = self::OPTION_SKIP_MISSING_NODE_DATA;
			if ($this->isNodeMarkedAsOptional($rootNodeName) || true === $this->isOptionIsEnabled($option)) {
				$appendedDataCount = 0;
			}
		}

		/**
		 * @var $nodes DOMDocument[]
		 */
		$nodes = array();

		for ($i = 0; $i < $appendedDataCount; ++$i) {
			$nodes[$i] = $this->domDocument->createElement($rootNodeName, '');
			$appendChild = false;

			if (!empty($this->nodeDefinitionAttributes[$rootNodeName])) {
				foreach ($this->nodeDefinitionAttributes[$rootNodeName] as $keyName => $attr) {
					$attributeValue = self::EMPTY_CELL_VALUE;
					$option = self::OPTION_HOLD_ON_MISSING_DATA_NODE_ATTRIBUTE;

					if (!empty($appendedData[$i][$keyName])) {
						$appendChild = true;
						$attributeValue = $appendedData[$i][$keyName];
					}
					elseif (true === $this->isOptionIsEnabled($option)) {
						$message = sprintf('Missing assigned value for attribute: "%s" on node: "%s". Assign is required!', $rootNodeName, $keyName);
						throw new \Exception($message);
					}

					$option = self::OPTION_SHOW_MISSING_NODE_ATTRIBUTES_AS_EMPTY;
					if (true === $this->isOptionIsEnabled($option)) {
						$appendChild = true;
					}

					if (true === $appendChild) {
						$nodeAttribute = $this->domDocument->createAttribute($keyName);
						$nodeAttribute->value = $attributeValue;
						$nodes[$i]->appendChild($nodeAttribute);
					}
				}
			}

			foreach ($childNodes as $rootNodeChild => $childNodeChild) {
				$subNodes = $this->createNode($rootNodeChild, $childNodeChild);
				foreach ($subNodes as $subNode) {
					$nodes[$i]->appendChild($subNode);
				}
			}

		}

		return $nodes;
	}

	/**
	 * Parses a string and returns the attributes width values als stdClass object.
	 *
	 * @param    string $string
	 *
	 * @return \stdClass
	 */
	private function getAttributesByString($string) {
		// support "data-*" attributes
		$regEx = '~(?<attributeName>[a-z0-9-]+)\s*=\s*("|\')(?<attributeValue>[^\2]*)\2~Uis';
		$counter = preg_match_all($regEx, $string, $matches, PREG_SET_ORDER);
		$result = new \stdClass();
		if ($counter) {
			foreach ($matches as $match) {
				$attribute = trim(strtolower($match['attributeName']));
				$result->{$attribute} = $match['attributeValue'];
			}
		}

		return $result;
	}

	/**
	 * Returns mach of parsed identifier.
	 *
	 * @param    string $identifier
	 * @param    string $text
	 *
	 * @return array
	 */
	private function parseByIdentifier($identifier, $text) {
		$regEx = sprintf('~<xs:%s (?<attr>[^>]+)/>~Uis', preg_quote($identifier));
		preg_match_all($regEx, $text, $matches, PREG_SET_ORDER);

		return $matches;
	}

	/**
	 * Get all node references of this element by parsing.
	 *
	 * @param    $string
	 *
	 * @return array
	 */
	private function getNodeReferences($string) {
		$refs = array();
		$allXsElements = $this->parseByIdentifier('element', $string);
		foreach ($allXsElements as $xsElement) {
			$attributes = $this->getAttributesByString($xsElement['attr']);
			$refs[$attributes->ref] = true;
		}

		return $refs;
	}

	/**
	 * Get all node attributes of this element by parsing.
	 *
	 * @param    $string
	 *
	 * @return array
	 */
	private function getAllNodeAttributes($string) {
		$allAttributes = array();
		$allXsAttributes = $this->parseByIdentifier('attribute', $string);
		foreach ($allXsAttributes as $xsElement) {
			$attributes = $this->getAttributesByString($xsElement['attr']);
			$attributesClone = clone $attributes;
			unset($attributesClone->name);
			$allAttributes[$attributes->name] = $attributesClone;
		}

		return $allAttributes;
	}

	/**
	 * Parsed the XSD-Markup and generate a xml structure from that.
	 *
	 * @return void
	 */
	private function parseStructure() {

		// get all xs::elements
		$regEx = '~<xs:element (?<attr>[^>]+)>(.*)</xs:element>~Uis';
		preg_match_all($regEx, $this->xsdSource, $allXsElements, PREG_SET_ORDER);
		foreach ($allXsElements as $xsElement) {
			$attributes = $this->getAttributesByString($xsElement['attr']);
			$elementName = $attributes->name;

			// get all refs to element
			$refs = $this->getNodeReferences($xsElement['2']);
			if (!empty($refs)) {
				foreach ($refs as $key => $null) {
					// register element reference
					$this->xmlReference[$key] = $elementName;
				}
			}

			// get all attributes
			$allAttributes = $this->getAllNodeAttributes($xsElement['2']);
			if (!empty($allAttributes)) {
				foreach ($allAttributes as $key => $value) {
					$this->nodeDefinitionAttributes[$elementName][$key] = $value;
				}
			}
		}

		$elements = array();

		foreach ($this->xmlReference as $key => $val) {
			$elements[$key] = array();
			$elements[$val] = array();
		}

		$counterElements = count($elements);
		for ($i = 0; $i < $counterElements; ++$i) {
			foreach ($this->xmlReference as $searchNode => $intoNode) {
				$this->addNodesToStructure($elements, $searchNode, $intoNode);
			}
		}

		$this->xmlStructure = $elements;
	}

	/**
	 * Assign custom data.
	 *
	 * @param string $key
	 * @param array $data
	 */
	public function appendNodeData($key, array $data) {
		if (!isset($this->assignedData[$key])) {
			$this->assignedData[$key] = array();
		}

		$this->assignedData[$key][] = $data;
	}

	/**
	 * Grabs the value of a key on this array recursive.
	 *
	 * @param    array $elements
	 * @param    string $key
	 *
	 * @return array
	 */
	private function arrayValuePicker(&$elements, $key) {
		if (isset($elements[$key])) {
			$value = $elements[$key];
			unset($elements[$key]);

			return array($key => $value);
		}
		else {
			foreach ($elements as &$val) {
				return $this->arrayValuePicker($val, $key);
			}
		}

		return array();
	}

	/**
	 * Tamps a array by the given key into the $structure array. Detects the iteration loop.
	 *
	 * @param    array $structure
	 * @param    string $key
	 * @param    array $value
	 */
	private function arrayValueTamper(&$structure, $key, array $value) {
		if (isset($structure[$key])) {
			$structure[$key] = array_replace_recursive($structure[$key], $value);
		}
		else {
			foreach ($structure as &$val) {
				$this->arrayValueTamper($val, $key, $value);
			}
		}
	}

	/**
	 * This function add a node under consideration from children to xml structure recursive.
	 *
	 * @param array $structure
	 * @param string $searchNode
	 * @param string $intoNode
	 *
	 * @return null
	 */
	private function addNodesToStructure(&$structure, $searchNode, $intoNode) {
		$fromValue = $this->arrayValuePicker($structure, $searchNode);
		$this->arrayValueTamper($structure, $intoNode, $fromValue);
	}

	/**
	 * Returns the required data they have to be assigned.
	 *
	 * @return string|array
	 */
	public function getRequiredData($returnAsString = true) {
		$required = array();
		$this->parseStructure();

		foreach ($this->nodeDefinitionAttributes as $key => $data) {
			$attributes = array();
			foreach ($data as $attributeName => $definition) {
				$restriction = 'no restrictions given';
				if (isset($definition->type)) {
					$restriction = $definition->type;
				}
				$attributes[$attributeName] = $restriction;
			}
			$required[$key] = $attributes;
		}

		if (true === $returnAsString) {
			return print_r($required, true);
		}
		else {
			return $required;
		}
	}

	/**
	 * Returns the required data as php-code snippet.
	 *
	 * @param    string $variable
	 *
	 * @return string
	 */
	public function getRequiredDataToPHP($variable = '$vulcanoXsdToXmlGenerator') {
		$data = $this->getRequiredData(false);
		$result = array();
		$result[] = '<?php' . PHP_EOL;
		foreach ($data as $assignKey => $fields) {
			$result[] = '';
			$result[] = "{$variable}->appendNodeData('{$assignKey}', array(";
			foreach (array_keys($fields) as $fieldName) {
				$result[] = "\t'{$fieldName}'\t\t=>\t'',";
			}
			$result[] = "));";
		}

		$phpCode = implode("\n", $result);

		if (PHP_SAPI === 'cli') {
			return $phpCode;
		}

		return highlight_string($phpCode, true);
	}
}