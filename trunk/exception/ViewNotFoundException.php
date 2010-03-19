<?php
class ViewNotFoundException extends UbarBaseException {

	public function __construct($viewPath) {
		// TODO: get real message
		$message = "The view file \"" . $viewPath . "\" was not found.";
		// make sure everything is assigned properly
		parent :: __construct($message, $this->getCodeFromProperties());
	}
}
?>