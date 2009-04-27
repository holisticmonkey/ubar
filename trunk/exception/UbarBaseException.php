<?php

abstract class UbarBaseException extends Exception {
	
	function getCodeFromProperties() {
		// TODO: get code from ubar_exception_mappings
		return '0000';
	}
}
?>
