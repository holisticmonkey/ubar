<?php
/**
 * Class definition for Result
 * @package		core
 */

/**
 * Container for result definitions.
 *
 * The class, Result, is a container for messages to be surfaced to the user.
 * It has no knowledge of the original arguments or message key, being the
 * rendered version for display. It does, however, have an optional association
 * with an input field allowing, for example, inline error display.
 *
 * This class encapsulates result definitions as found in the action config,
 * (ubar.xml). Beyond some basic value conversions, it also provides a result
 * generation static method.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 */
class Result {

	/**
	 * @param string $type Result type.
	 */
	private $type;

	/**
	 * @param string $name Result name, used for lookup.
	 */
	private $name;

	/**
	 * @param string $target Result target to render, forward to, etc.
	 */
	private $target;

	/**
	 * @param string $viewLocation Location of the view file, if defined.
	 */
	private $viewLocation;

	/**
	 * @param string $templateName Name of the template associated with the
	 * view, if defined.
	 */
	private $templateName;


	/**
	 * Construct the result definition from the contents of the ubar.xml file.
	 *
	 * @param class $xmlObj - XML representation of an result definition.
	 *
	 * @see ActionDef::getResult()
	 * @see Result::getGlobalResult()
	 */
	public function __construct($xmlObj) {
		$this->type = isset ($xmlObj['type']) ? (string) $xmlObj['type'] : GlobalConstants :: DEFAULT_TYPE;
		$this->name = isset ($xmlObj['name']) ? (string) $xmlObj['name'] : GlobalConstants :: DEFAULT_NAME;
		$this->target = (string) $xmlObj;
		if ($this->type == GlobalConstants :: PAGE_TYPE && $this->target != '') {
			$this->viewLocation = FileUtils :: dotToPath($this->target);
		}
		if (isset($xmlObj['template'])) {
			$this->templateName = (string) $xmlObj['template'];
		}
	}

	// if you need to fabricate a result manually
	/**
	 * Make a result definition with the given values. This is used in the
	 * Dispatcher to render a view by default if the result is success and
	 * a view is defined for the action.
	 *
	 * @param string $name Name of result, defaults to null.
	 * @param string $type Type of result, defaults to null.
	 * @param string $target Target of result, defaults to null.
	 *
	 * @return class Result with the given params.
	 *
	 * @see Dispatcher:;dispatch()
	 */
	public static function makeResult( $name = null, $type = null, $target = null) {
		$resultString = "<result name=\"$name\" type=\"$type\">$target</result>";
		$xmlObj = new SimpleXMLElement($resultString);
		return new Result($xmlObj);
	}

	/**
	 * Get result name, used for result lookup.
	 *
	 * @return string Result name.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get result type, used to determine how to manage or render the result.
	 *
	 * @return string Result type.
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get result target, an action, path, url, or file location.
	 *
	 * @return string Result target.
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * Get result templateName, used in page render.
	 *
	 * @return string Result templateName.
	 */
	public function getTemplateName() {
		return $this->templateName;
	}

	/**
	 * Get location of view file.
	 *
	 * @return string Result view location.
	 */
	public function getViewLocation() {
		return $this->viewLocation;
	}
}
?>
