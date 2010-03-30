<?php
/**
 * Class definition for ActionNotFoundException
 * @package core
 */

 /**
 * Action not found exception
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
class ActionNotFoundException extends UbarBaseException {

	/**
	 * Create the exception from the action definition.
	 *
	 * @param class $actionDef The action definition.
	 *
	 * @todo Get message from properties file? Possibly dangerous in exceptions.
	 */
	public function __construct($actionDef) {
		// assemble message
		$message = "The action class \"" . $actionDef->getClassName() . "\" was not found at \"" . BASE_ACTION_PATH . $actionDef->getActionLocation() . "\".";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>