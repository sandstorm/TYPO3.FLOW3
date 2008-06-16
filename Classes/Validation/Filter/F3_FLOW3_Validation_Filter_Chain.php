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
 * @subpackage Validation
 * @version $Id:F3_FLOW3_Validation_Filter_Chain.php 467 2008-02-06 19:34:56Z robert $
 */

/**
 * A filter to chain many filters
 *
 * @package FLOW3
 * @subpackage Validation
 * @version $Id:F3_FLOW3_Validation_Filter_Chain.php 467 2008-02-06 19:34:56Z robert $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class F3_FLOW3_Validation_Filter_Chain implements F3_FLOW3_Validation_FilterInterface {

	/**
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Returns the filtered subject.
	 *
	 * @param object The subject that should be filtered
	 */
	public function filter($subject, F3_FLOW3_Validation_Errors &$errors) {
		foreach ($this->filters as $filter) {
			$filter->filter($subject, $errors);
		}
	}

	/**
	 * Adds a new filter to the chain. Returns the index of the chain entry.
	 *
	 * @param F3_FLOW3_Validation_FilterInterface The filter that should be added
	 * @return integer The index of the new chain entry
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function addFilter(F3_FLOW3_Validation_FilterInterface $filter) {
		$this->filters[] = $filter;
		return count($this->filters) - 1;
	}

	/**
	 * Returns the filter with the given index of the chain.
	 *
	 * @param  integer The index of the filter that should be returned
	 * @return F3_FLOW3_Validation_FilterInterface The requested filter
	 * @throws F3_FLOW3_Validation_Exception_InvalidChainIndex
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getFilter($index) {
		if (!isset($this->filters[$index])) throw new F3_FLOW3_Validation_Exception_InvalidChainIndex('Invalid chain index.', 1207215864);
		return $this->filters[$index];
	}

	/**
	 * Removes the filters with the given index of the chain.
	 *
	 * @param integer The index of the filter that should be removed
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function removeFilter($index) {
		if (!isset($this->filters[$index])) throw new F3_FLOW3_Validation_Exception_InvalidChainIndex('Invalid chain index.', 1207020177);
		unset($this->filters[$index]);
	}
}

?>