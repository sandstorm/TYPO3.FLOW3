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
 * @subpackage Tests
 * @version $Id$
 */

require_once('Fixture/F3_FLOW3_Tests_AOP_Fixture_ClassTaggedWithSomething.php');

/**
 * Testcase for the Pointcut Class-Tagged-With Filter
 *
 * @package FLOW3
 * @subpackage Tests
 * @version $Id:F3_FLOW3_AOP_PointcutClassFilterTest.php 201 2007-03-30 11:18:30Z robert $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_AOP_PointcutClassTaggedWithFilterTest extends F3_Testing_BaseTestCase {

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function filterMatchesClassWithSimpleTag() {
		$classTaggedWithFilter = new F3_FLOW3_AOP_PointcutClassTaggedWithFilter('something');
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_AOP_Fixture_ClassTaggedWithSomething');
		$methods = $class->getMethods();
		$this->assertTrue($classTaggedWithFilter->matches($class, $methods[0], microtime()));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function filterMatchesClassWithTagWithWildcard() {
		$classTaggedWithFilter = new F3_FLOW3_AOP_PointcutClassTaggedWithFilter('some.*');
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_AOP_Fixture_ClassTaggedWithSomething');
		$methods = $class->getMethods();
		$this->assertTrue($classTaggedWithFilter->matches($class, $methods[0], microtime()));
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function filterCorrectlyDoesntMatchClassWithoutSpecifiedTag() {
		$classTaggedWithFilter = new F3_FLOW3_AOP_PointcutClassTaggedWithFilter('any.*');
		$class = new F3_FLOW3_Reflection_Class('F3_FLOW3_Tests_AOP_Fixture_ClassTaggedWithSomething');
		$methods = $class->getMethods();
		$this->assertFALSE($classTaggedWithFilter->matches($class, $methods[0], microtime()));
	}

}
?>