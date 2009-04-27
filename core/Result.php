<?php
class Result {

	private $type;
	private $name;
	private $target;

	public function __construct($xmlObj) {
		$this->type = isset ($xmlObj['type']) ? (string) $xmlObj['type'] : DEFAULT_TYPE;
		$this->name = (string) $xmlObj['type'];
		$this->target = (string) $xmlObj;
	}
}
?>
