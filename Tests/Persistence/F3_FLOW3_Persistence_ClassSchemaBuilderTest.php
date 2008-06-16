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

require_once('Fixture/F3_FLOW3_Tests_Persistence_Fixture_Repository1.php');
require_once('Fixture/F3_FLOW3_Tests_Persistence_Fixture_Entity1.php');
require_once('Fixture/F3_FLOW3_Tests_Persistence_Fixture_ValueObject1.php');

/**
 * Testcase for the Class Schema Builder
 *
 * @package FLOW3
 * @subpackage Persistence
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_Persistence_ClassSchemaBuilderTest extends F3_Testing_BaseTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function classSchemaOnlyContainsNonTransientProperties() {
		$expectedProperties = array('someString', 'someInteger', 'someFloat', 'someDate', 'someBoolean');

		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_Entity1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$actualProperties = array_keys($builtClassSchema->getProperties());
		sort($expectedProperties);
		sort($actualProperties);
		$this->assertEquals($expectedProperties, $actualProperties);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function propertyTypesAreDetectedFromVarAnnotations() {
		$expectedProperties = array(
			'someBoolean' => 'boolean',
			'someString' => 'string',
			'someInteger' => 'integer',
			'someFloat' => 'float',
			'someDate' => 'DateTime'
		);

		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_Entity1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$actualProperties = $builtClassSchema->getProperties();
		asort($expectedProperties);
		asort($actualProperties);
		$this->assertEquals($expectedProperties, $actualProperties);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function modelTypeRepositoryIsRecognizedByRepositoryAnnotation() {
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_Repository1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$this->assertEquals($builtClassSchema->getModelType(), F3_FLOW3_Persistence_ClassSchema::MODELTYPE_REPOSITORY);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function modelTypeEntityIsRecognizedByValueObjectAnnotation() {
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_Entity1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$this->assertEquals($builtClassSchema->getModelType(), F3_FLOW3_Persistence_ClassSchema::MODELTYPE_ENTITY);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function modelTypeValueObjectIsRecognizedByValueObjectAnnotation() {
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_ValueObject1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$this->assertEquals($builtClassSchema->getModelType(), F3_FLOW3_Persistence_ClassSchema::MODELTYPE_VALUEOBJECT);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function classSchemaContainsNameOfItsRelatedClass() {
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_Persistence_Fixture_Entity1');
		$builtClassSchema = F3_FLOW3_Persistence_ClassSchemaBuilder::build($class);
		$this->assertEquals($builtClassSchema->getClassName(), 'F3_FLOW3_Tests_Persistence_Fixture_Entity1');
	}
}

?>