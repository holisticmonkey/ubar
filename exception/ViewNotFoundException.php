<?php
class ViewNotFoundException extends UbarBaseException {

	public function __construct($actionDef) {
		// TODO: get real message
		$message = "The view file \"" . BASE_VIEW_PATH . $actionDef->getViewLocation() . "\" was not found.";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>