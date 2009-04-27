<?php
class ActionNotFoundException extends UbarBaseException {

	// Redefine the exception so message isn't optional
	public function __construct($action) {
		// get translated message with substituted arguments
		// TODO: get real message
		$message = "The action \"$action\" was not found";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>