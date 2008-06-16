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
 * Class Schema Builder
 *
 * @package FLOW3
 * @subpackage Persistence
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_Persistence_ClassSchemaBuilder {

	/**
	 * Builds a class schema from the specified class
	 *
	 * @param F3_FLOW3_Reflection_Class $class Reflection of the class to be analyzed
	 * @return F3_FLOW3_Persistence_ClassSchema
	 * @author Robert Lemke <robert@typo3.org>
	 * @throws F3_FLOW3_Persistence_Exception_InvalidClass if the specified class does not exist
	 */
	static public function build(F3_FLOW3_Reflection_Class $class) {
		$classSchema = new F3_FLOW3_Persistence_ClassSchema($class->getName());

		if ($class->isTaggedWith('entity')) {
			$classSchema->setModelType(F3_FLOW3_Persistence_ClassSchema::MODELTYPE_ENTITY);
		} elseif ($class->isTaggedWith('repository')) {
			$classSchema->setModelType(F3_FLOW3_Persistence_ClassSchema::MODELTYPE_REPOSITORY);
		} elseif ($class->isTaggedWith('valueobject')) {
			$classSchema->setModelType(F3_FLOW3_Persistence_ClassSchema::MODELTYPE_VALUEOBJECT);
		}

		foreach ($class->getProperties() as $property) {
			if (!$property->isTaggedWith('transient') && $property->isTaggedWith('var')) {
				$classSchema->setProperty($property->getName(), implode(' ', $property->getTagValues('var')));
			}
		}

		return $classSchema;
	}

}
?>