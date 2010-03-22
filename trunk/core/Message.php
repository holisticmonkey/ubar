<?php
// wrapper object for messages such as errors, warnings, and info
// used instead of associative array of field names and messages in session object
// due to possibility of multiple messages for the same field name
// TODO: consider adding type and other pertinent info
class Message {

	private $fieldName;

	private $message;

	public function __construct($message, $fieldName = null) {
		$this->message = $message;
		$this->fieldName = $fieldName;
	}

	public function getFieldName() {
		return $this->fieldName;
	}

	public function getMessage() {
		return $this->message;
	}
}
?>