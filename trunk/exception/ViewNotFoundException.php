<?php
/**
 * Class definition for ViewNotFoundException
 * @package core
 */

 /**
 * View not found exception
 *
 * Exception thrown when an action file is not found at the specified location.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	exceptions
 */
class ViewNotFoundException extends UbarBaseException {

	/**
	 * Create the exception using the view path.
	 *
	 * @param class $viewPath The path to the missing view file.
	 *
	 * @todo Get message from properties file? Possibly dangerous in exceptions.
	 */
	public function __construct($viewPath) {
		// assemblemessage
		$message = "The view file \"" . $viewPath . "\" was not found.";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>