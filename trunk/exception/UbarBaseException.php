<?php
/**
 * Class definition for UbarBaseException
 * @package core
 */

 /**
 * Base class for framework exceptions
 *
 * This is the base class for for framework exceptions. For now it is merely
 * used to catching framework exceptions specifically. However, future releases
 * will contain more functionality common across framework exceptions.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	exceptions
 */
abstract class UbarBaseException extends Exception {

	/**
	 * Get code from exceptions mapping properties file.
	 *
	 * NOTE: This has not yet been implemented.
	 *
	 * @return string Exception code from props file.
	 *
	 * @todo Enable the function or remove it as unnecessary.
	 */
	public function getCodeFromProperties() {
		// TODO: get code from ubar_exception_mappings
		return '0000';
	}
}
?>
