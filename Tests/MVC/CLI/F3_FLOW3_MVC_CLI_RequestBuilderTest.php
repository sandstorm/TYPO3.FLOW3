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
 * Testcase for the MVC CLI Request Builder
 * 
 * @package		FLOW3
 * @version 	$Id:F3_FLOW3_Component_TransientObjectCacheTest.php 201 2007-03-30 11:18:30Z robert $
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_MVC_CLI_RequestBuilderTest extends F3_Testing_BaseTestCase {

	/**
	 * @var F3_FLOW3_MVC_CLI_RequestBuilder
	 */
	protected $requestBuilder;
	
	/**
	 * @var F3_FLOW3_Utility_MockEnvironment
	 */
	protected $environment;
	
	/**
	 * Sets up this test case
	 *
	 * @author  Robert Lemke <robert@typo3.org>
	 */
	public function setUp() {
		$this->environment = $this->componentManager->getComponent('F3_FLOW3_Utility_MockEnvironment');
		$this->requestBuilder = $this->componentManager->getComponent('F3_FLOW3_MVC_CLI_RequestBuilder', $this->componentManager, $this->environment);
	}

	/**
	 * Checks if a CLI request without any arguments results in the expected request object
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function simpleCLIAccessBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 1;
		$this->environment->SERVER['argv'][0] = 'index.php'; 
	
		$request = $this->requestBuilder->build();
		$this->assertEquals('F3_FLOW3_MVC_Controller_DefaultController', $request->getControllerName(), 'The CLI request without any arguments did not return a request object pointing to the default controller.');
	}
	
	/**
	 * Checks if a CLI request with a package name argument results in the expected request object
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function CLIAccessWithPackageNameBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 2;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';

		$request = $this->requestBuilder->build();
		$this->assertEquals('F3_TestPackage_Controller_Default', $request->getControllerName(), 'The CLI request specifying a package name did not return a request object pointing to the expected controller.');
	}

	/**
	 * Checks if a CLI request specifying a package and a controller name results in the expected request object
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function CLIAccessWithPackageAndControllerNameBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 3;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		
		$request = $this->requestBuilder->build();
		$this->assertEquals('F3_TestPackage_Controller_Default', $request->getControllerName(), 'The CLI request specifying a package name and controller did not return a request object pointing to the expected controller.');
	}

	/**
	 * Checks if a CLI request specifying a package, controller and action name results in the expected request object
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function checkIfCLIAccessWithPackageControllerAndActionNameBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 4;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		$this->environment->SERVER['argv'][3] = 'list';
		
		$request = $this->requestBuilder->build();
		$this->assertEquals('F3_TestPackage_Controller_Default', $request->getControllerName(), 'The CLI request specifying a package name and controller did not return a request object pointing to the expected controller.');
		$this->assertEquals('list', $request->getActionName(), 'The CLI request specifying a package, controller and action name did not return a request object pointing to the expected action.');
	}
	
	/**
	 * Checks if a CLI request specifying some "console style" (--my-argument=value) arguments results in the expected request object
	 * 
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function CLIAccesWithPackageControllerActionAndArgumentsBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 6;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		$this->environment->SERVER['argv'][3] = 'list';
		$this->environment->SERVER['argv'][4] = '--test-argument=value';
		$this->environment->SERVER['argv'][5] = '--test-argument2=value2';
		
		$request = $this->requestBuilder->build();
		$this->assertTrue($request->hasArgument('testArgument'), 'The given "testArgument" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument2'), 'The given "testArgument2" was not found in the built request.');
		$this->assertEquals($request->getArgument('testArgument'), 'value', 'The "testArgument" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument2'), 'value2', 'The "testArgument2" had not the given value.');
	}
	
	/**
	 * Checks if a CLI request specifying some "console style" (--my-argument =value) arguments with spaces between name and value results in the expected request object
	 * 
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function checkIfCLIAccesWithPackageControllerActionAndArgumentsToleratesSpaces() {
		$this->environment->SERVER['argc'] = 12;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		$this->environment->SERVER['argv'][3] = 'list';
		$this->environment->SERVER['argv'][4] = '--test-argument=';
		$this->environment->SERVER['argv'][5] = 'value';
		$this->environment->SERVER['argv'][6] = '--test-argument2';
		$this->environment->SERVER['argv'][7] = '=value2';
		$this->environment->SERVER['argv'][8] = '--test-argument3';
		$this->environment->SERVER['argv'][9] = '=';
		$this->environment->SERVER['argv'][10] = 'value3';
		$this->environment->SERVER['argv'][11] = '--test-argument4=value4';

		$request = $this->requestBuilder->build();
		$this->assertTrue($request->hasArgument('testArgument'), 'The given "testArgument" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument2'), 'The given "testArgument2" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument3'), 'The given "testArgument3" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument4'), 'The given "testArgument4" was not found in the built request.');
		$this->assertEquals($request->getArgument('testArgument'), 'value', 'The "testArgument" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument2'), 'value2', 'The "testArgument2" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument3'), 'value3', 'The "testArgument3" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument4'), 'value4', 'The "testArgument4" had not the given value.');
	}
	
	/**
	 * Checks if a CLI request specifying some short "console style" (-c value or -c=value or -c = value) arguments results in the expected request object
	 * 
	 * @test
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function CLIAccesWithShortArgumentsBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 10;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		$this->environment->SERVER['argv'][3] = 'list';
		$this->environment->SERVER['argv'][4] = '-d';
		$this->environment->SERVER['argv'][5] = 'valued';
		$this->environment->SERVER['argv'][6] = '-f=valuef';
		$this->environment->SERVER['argv'][7] = '-a';
		$this->environment->SERVER['argv'][8] = '=';
		$this->environment->SERVER['argv'][9] = 'valuea';
		
		$request = $this->requestBuilder->build();
		$this->assertTrue($request->hasArgument('d'), 'The given "d" was not found in the built request.');
		$this->assertTrue($request->hasArgument('f'), 'The given "f" was not found in the built request.');
		$this->assertTrue($request->hasArgument('a'), 'The given "a" was not found in the built request.');
		$this->assertEquals($request->getArgument('d'), 'valued', 'The "d" had not the given value.');
		$this->assertEquals($request->getArgument('f'), 'valuef', 'The "f" had not the given value.');
		$this->assertEquals($request->getArgument('a'), 'valuea', 'The "a" had not the given value.');
	}
	
	/**
	 * Checks if a CLI request specifying some mixed "console style" (-c or --my-argument -f=value) arguments with and without values results in the expected request object
	 * 
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function CLIAccesWithArgumentsWithAndWithoutValuesBuildsCorrectRequest() {
		$this->environment->SERVER['argc'] = 27;
		$this->environment->SERVER['argv'][0] = 'index.php';
		$this->environment->SERVER['argv'][1] = 'TestPackage';
		$this->environment->SERVER['argv'][2] = 'Default';
		$this->environment->SERVER['argv'][3] = 'list';
		$this->environment->SERVER['argv'][4] = '--test-argument=value';
		$this->environment->SERVER['argv'][5] = '--test-argument2=';
		$this->environment->SERVER['argv'][6] = 'value2';
		$this->environment->SERVER['argv'][7] = '-k';
		$this->environment->SERVER['argv'][8] = '--test-argument-3';
		$this->environment->SERVER['argv'][9] = '=';
		$this->environment->SERVER['argv'][10] = 'value3';
		$this->environment->SERVER['argv'][11] = '--test-argument4=value4';
		$this->environment->SERVER['argv'][12] = '-f';
		$this->environment->SERVER['argv'][13] = 'valuef';
		$this->environment->SERVER['argv'][14] = '-d=valued';
		$this->environment->SERVER['argv'][15] = '-a';
		$this->environment->SERVER['argv'][16] = '=';
		$this->environment->SERVER['argv'][17] = 'valuea';
		$this->environment->SERVER['argv'][18] = '-c';
		$this->environment->SERVER['argv'][19] = '--testArgument7';
		$this->environment->SERVER['argv'][20] = '--test-argument5';
		$this->environment->SERVER['argv'][21] = '=';
		$this->environment->SERVER['argv'][22] = '5';
		$this->environment->SERVER['argv'][23] = '--test-argument6';
		$this->environment->SERVER['argv'][24] = '-j';
		$this->environment->SERVER['argv'][25] = 'kjk';
		$this->environment->SERVER['argv'][26] = '-m';
		
		$request = $this->requestBuilder->build();
		$this->assertTrue($request->hasArgument('testArgument'), 'The given "testArgument" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument2'), 'The given "testArgument2" was not found in the built request.');
		$this->assertTrue($request->hasArgument('k'), 'The given "k" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument3'), 'The given "testArgument3" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument4'), 'The given "testArgument4" was not found in the built request.');
		$this->assertTrue($request->hasArgument('f'), 'The given "f" was not found in the built request.');
		$this->assertTrue($request->hasArgument('d'), 'The given "d" was not found in the built request.');
		$this->assertTrue($request->hasArgument('a'), 'The given "a" was not found in the built request.');
		$this->assertTrue($request->hasArgument('c'), 'The given "d" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument7'), 'The given "testArgument7" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument5'), 'The given "testArgument5" was not found in the built request.');
		$this->assertTrue($request->hasArgument('testArgument6'), 'The given "testArgument6" was not found in the built request.');
		$this->assertTrue($request->hasArgument('j'), 'The given "j" was not found in the built request.');
		$this->assertTrue($request->hasArgument('m'), 'The given "m" was not found in the built request.');
		$this->assertEquals($request->getArgument('testArgument'), 'value', 'The "testArgument" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument2'), 'value2', 'The "testArgument2" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument3'), 'value3', 'The "testArgument3" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument4'), 'value4', 'The "testArgument4" had not the given value.');
		$this->assertEquals($request->getArgument('f'), 'valuef', 'The "f" had not the given value.');
		$this->assertEquals($request->getArgument('d'), 'valued', 'The "d" had not the given value.');
		$this->assertEquals($request->getArgument('a'), 'valuea', 'The "a" had not the given value.');
		$this->assertEquals($request->getArgument('testArgument5'), '5', 'The "testArgument4" had not the given value.');
		$this->assertEquals($request->getArgument('j'), 'kjk', 'The "j" had not the given value.');
	}
}
?>