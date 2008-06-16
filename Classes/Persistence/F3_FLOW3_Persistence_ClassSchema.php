<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package FLOW3
 * @subpackage Persistence
 * @version $Id$
 */

/**
 * A class schema
 *
 * @package FLOW3
 * @subpackage Persistence
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_Persistence_ClassSchema {

	const MODELTYPE_REPOSITORY = 1;
	const MODELTYPE_ENTITY = 2;
	const MODELTYPE_VALUEOBJECT = 3;

	/**
	 * Name of the class this schema is referring to
	 *
	 * @var string
	 */
	protected $className;

	/**
	 * Model type of the class this schema is referring to
	 *
	 * @var integer
	 */
	protected $modelType = self::MODELTYPE_ENTITY;

	/**
	 * Properties of the class which need to be persisted
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * Constructs this class schema
	 *
	 * @param string $className Name of the class this schema is referring to
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($className) {
		$this->className = $className;
	}

	/**
	 * Returns the class name this schema is referring to
	 *
	 * @return string The class name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * Sets (defines) a specific property and its type.
	 *
	 * @param string $name Name of the property
	 * @param string $type Type of the property (ie. one of "integer", "float", "boolean", "string", "array of xy" or some class typ
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setProperty($name, $type) {
		$this->properties[$name] = $type;
	}

	/**
	 * Returns all properties defined in this schema
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Sets the model type of the class this schema is referring to.
	 *
	 * @param integer The model type, one of the MODELTYPE_* constants.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setModelType($modelType) {
		if ($modelType < 1 || $modelType > 3) throw new InvalidArgumentException('"' . $modelType . '" is an invalid model type.', 1212519195);
		$this->modelType = $modelType;
	}

	/**
	 * Returns the model type of the class this schema is referring to.
	 *
	 * @return integer The model type, one of the MODELTYPE_* constants.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getModelType() {
		return $this->modelType;
	}

	/**
	 * If the class schema has a certain property
	 *
	 * @param string $propertyName Name of the property
	 * @return boolean
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function hasProperty($propertyName) {
		return key_exists($propertyName, $this->properties);
	}

}
?>