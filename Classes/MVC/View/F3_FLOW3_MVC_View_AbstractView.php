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
 * @subpackage MVC
 * @version $Id:F3_FLOW3_MVC_View_AbstractView.php 467 2008-02-06 19:34:56Z robert $
 */

/**
 * An abstract View
 *
 * @package FLOW3
 * @subpackage MVC
 * @version $Id:F3_FLOW3_MVC_View_AbstractView.php 467 2008-02-06 19:34:56Z robert $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class F3_FLOW3_MVC_View_AbstractView {

	/**
	 * @var F3_FLOW3_Component_ManagerInterface A reference to the Component Manager
	 */
	protected $componentManager;

	/**
	 * @var F3_FLOW3_Package_ManagerInterface A reference to the Package Manager
	 */
	protected $packageManager;

	/**
	 * @var F3_FLOW3_Resource_ManagerInterface
	 */
	protected $resourceManager;

	/**
	 * Constructs the view.
	 *
	 * @param F3_FLOW3_Component_ManagerInterface $componentManager A reference to the Component Manager
	 * @param F3_FLOW3_Package_ManagerInterface $packageManager A reference to the Package Manager
	 * @param F3_FLOW3_Resource_Manager $resourceManager A reference to the Resource Manager
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct(F3_FLOW3_Component_ManagerInterface $componentManager, F3_FLOW3_Package_ManagerInterface $packageManager, F3_FLOW3_Resource_Manager $resourceManager) {
		$this->componentManager = $componentManager;
		$this->packageManager = $packageManager;
		$this->resourceManager = $resourceManager;
		$this->initializeView();
	}

	/**
	 * Initializes this view.
	 *
	 * Override this method for initializing your concrete view implementation.
	 *
	 * @return void
	 */
	protected function initializeView() {
	}

	/**
	 * Renders the view
	 *
	 * @return string The rendered view
	 */
	abstract public function render();
}

?>