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
 * @version $Id:F3_FLOW3_Component_ClassLoaderTest.php 201 2007-03-30 11:18:30Z robert $
 */

/**
 * Testcase for the resource processor
 *
 * @package FLOW3
 * @version $Id:F3_FLOW3_Component_ClassLoaderTest.php 201 2007-03-30 11:18:30Z robert $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_Resource_ProcessorTest extends F3_Testing_BaseTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function canAdjustRelativePathsInHTML() {
		$originalHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<style type="text/css">
			.F3_WidgetLibrary_Widgets_FloatingWindow {
				background-image: url(DefaultView_FloatingWindow.png);
			}
		</style>
	</head>
	<body>
		<img src="DefaultView_Package.png" class="DefaultView_Package" />
	</body>
</html>';
		$expectedHTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<style type="text/css">
			.F3_WidgetLibrary_Widgets_FloatingWindow {
				background-image: url(test/prefix/to/insert/DefaultView_FloatingWindow.png);
			}
		</style>
	</head>
	<body>
		<img src="test/prefix/to/insert/DefaultView_Package.png" class="DefaultView_Package" />
	</body>
</html>';
		$processor = $this->componentManager->getComponent('F3_FLOW3_Resource_Processor');
		$processedHTML = $processor->adjustRelativePathsInHTML($originalHTML, 'test/prefix/to/insert/');
		$this->assertEquals($processedHTML, $expectedHTML, 'The processed HTML was not changed as expected.');
	}
}

?>