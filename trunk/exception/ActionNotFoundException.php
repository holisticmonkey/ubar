<?php
class ActionNotFoundException extends UbarBaseException {

	// Redefine the exception so message isn't optional
	public function __construct($actionDef) {
		// get translated message with substituted arguments
		// TODO: get real message
		$message = "The action class \"" . $actionDef->getClassName() . "\" was not found at \"" . BASE_ACTION_PATH . $actionDef->getActionLocation() . "\".";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>