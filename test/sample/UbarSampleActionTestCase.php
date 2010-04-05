<?php
/**
 * Base class for testing Actions or functionality that requires a setup context.
 * Note that a simulation of the config, controller, and views are nested in the test directory.
 */
require_once(__DIR__ . "/../UbarBaseActionTestCase.php");
abstract class UbarSampleActionTestCase extends UbarBaseActionTestCase {

	public function __construct() {
		// override the config properties
		// TODO: sort out how to put in database credentials without exposing to svn
		$UBAR_GLOB['UBAR_CONFIG_OVERRIDE'] = __DIR__ . "/ubar_sample_config.properties";

		// construct with sample action config
		parent::__construct(__DIR__ . "/ubar_sample.xml");
	}
}
?>