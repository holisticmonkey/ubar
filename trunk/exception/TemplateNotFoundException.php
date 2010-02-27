<?php
class TemplateNotFoundException extends UbarBaseException {

	public function __construct($actionDef) {
		// TODO: get real message
		$message = "The view \"" . $actionDef->getClassName() . "\" was not found at \"" . BASE_ACTION_PATH . $actionDef->getActionLocation() . "\".";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>
