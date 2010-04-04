<?php
/**
 * Class definition for ActionDef
 * @package		core
 */

/**
 * Container for action definitions.
 *
 * This class encapsulates action definitions as found in the action config,
 * (ubar.xml). Beyond some basic value conversions, it also provides a result
 * lookup functionality.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 */
class ActionDef {

	/**
	 * @var string Path to action file.
	 */
	private $actionLocation;

	/**
	 * @var string Name of action class.
	 */
	private $actionClassName;

	/**
	 * @var string Location of view file, if any.
	 */
	private $viewLocation;

	/**
	 * @var class Permission definitions (NOT IMPLEMENTED).
	 */
	private $permissions;

	/**
	 * @var class Result definitions.
	 */
	private $results;

	/**
	 * @var string Action name.
	 */
	private $name;

	/**
	 * @var string Template associated with action, if any.
	 */
	private $templateName;

	/**
	 * @var string Title of page, if any.
	 */
	private $title;

	/**
	 * @var string Key to lookup title in properties, if any.
	 */
	private $titleKey;

	/**
	 * @var string Page name, currently the same as the action name.
	 */
	private $page;

	/**
	 * @var string Section name, if any.
	 */
	private $section;

	/**
	 * @var string Sub-section name, if any.
	 */
	private $subSection;

	/**
	 * @var array Defined params for this action, if any.
	 */
	private $params = array ();

	/**
	 * Construct the action definition from the contents of the ubar.xml file.
	 *
	 * NOTE: If no path is defined for the action, a dummy action that always
	 * returns GlobalConstants::SUCCESS will be used.
	 *
	 * @param class $actionXML - XML representation of an action definition.
	 *
	 * @see ActionMapper::getAction()
	 */
	public function __construct($actionXML) {
		$path = ((string) $actionXML['path']);
		// use dummy action if no action defined
		if ($path != '') {
			$this->actionLocation = FileUtils :: dotToPath($path);
			$this->actionClassName = FileUtils :: classFromFile($this->actionLocation);
		} else {
			$this->actionClassName = GlobalConstants :: DUMMY_ACTION;
		}
		if (!is_null($actionXML['template'])) {
			$this->templateName = (string) $actionXML['template'];
		}
		if (!is_null($actionXML['view'])) {
			$this->viewLocation = FileUtils :: dotToPath((string) $actionXML['view']);
		}
		$this->results = $actionXML->results->result;
		$this->permissionGroup = $actionXML->permissionGroup;
		$this->permissions = $actionXML->permissions;
		$this->name = (string) $actionXML['name'];

		// add params
		foreach ($actionXML->param as $param) {
			$attribs = $param->attributes();
			$name = (string) $attribs->name;
			$value = (string) $attribs->value;
			$this->addParam($name, $value);
		}

		// display values
		$this->title = (string) $actionXML['title'];
		$this->titleKey = (string) $actionXML['titleKey'];
		// TODO: page mostly makes sense as action name, consider decoupling however
		$this->page = (string) $actionXML['name'];
		$this->section = (string) $actionXML['section'];
		$this->subSection = (string) $actionXML['subSection'];
	}

	/**
	 * Add a parameter to this definition.
	 *
	 * NOTE: This is only public so that ActionMapper may merge template
	 * definitions. Do not call this explicitly.
	 *
	 * @param string $name Name of the param to add.
	 * @param string $value Value of the param to add.
	 *
	 * @see ActionMapper::getTemplate()
	 */
	public function addParam($name, $value) {
		if (!array_key_exists($name, $this->params)) {
			$this->params[$name] = $value;
		}
	}

	/**
	 * Get the name of the template associated with this action definition.
	 *
	 * @return string The template name.
	 */
	public function getTemplateName() {
		return $this->templateName;
	}

	/**
	 * Get the location of the action php file associated with this action
	 * definition.
	 *
	 * @return string The path to the action file.
	 */
	public function getActionLocation() {
		return $this->actionLocation;
	}

	/**
	 * Override the action class location. Used when a dummy action is inferred.
	 */
	public function setActionLocation($location) {
		$this->actionLocation = $location;
	}

	/**
	 * Get the location of the view php file associated with this action
	 * definition, if any.
	 *
	 * @return string The path to the view file.
	 */
	public function getViewLocation() {
		return $this->viewLocation;
	}

	/**
	 * Get the class name of the action.
	 *
	 * @return string The class name of the action.
	 */
	public function getClassName() {
		return $this->actionClassName;
	}

	/**
	 * Override the action class name. Used when a dummy action is inferred.
	 */
	public function setClassName($name) {
		$this->actionClassName = $name;
	}

	/**
	 * Get the name of the action. This is the name used in the URL, not the
	 * class name.
	 *
	 * @return string The name of the action.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the title of the page, if any.
	 *
	 * @return string The title.
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the properties key of the title for the page, if any.
	 *
	 * @return string The title key.
	 */
	public function getTitleKey() {
		return $this->titleKey;
	}

	/**
	 * Get the page name, currently the same as the action name.
	 *
	 * @return string The page name.
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Get the section the page resides in if defined.
	 *
	 * @return string The section name.
	 */
	public function getSection() {
		return $this->section;
	}

	/**
	 * Get the sub-section the page resides in if defined.
	 *
	 * @return string The sub-section name.
	 */
	public function getSubSection() {
		return $this->subSection;
	}

	/**
	 * Test if something is allowed by checking against the permissions.
	 *
	 * NOTE: This is not yet implemented.
	 *
	 * @param mixed $permissionArgs The arguments are not yet determined.
	 *
	 * @return boolean Returns true if allowed.
	 */
	public function isAllowed($permissionArgs) {
		// is string? - assume permission group
		// no permission group defined? return true else check case insensitive with trimming

		// is array? - assume permissions
		// no permissions listed? return true else check case insensitive with trimming
		return true;
	}

	/**
	 * Get the result definition for a given result string. Returns null if none defined.
	 *
	 * NOTE: The Dispatcher will look in global results if not found here.
	 *
	 * @param string $resultString The name of the result you're trying to find.
	 *
	 * @return class Result definition for the given result name.
	 *
	 * @see Dispatcher::dispatch()
	 */
	public function getResult($resultString) {
		// find result object
		if (!is_null($this->results)) {
			foreach ($this->results as $result) {
				$name = (string) $result['name'];
				if ($name == $resultString || ($name == '' && $resultString == GlobalConstants :: SUCCESS)) {
					return new Result($result);
				}
			}
		}
		return null;
	}

	/**
	 * Get the param value for the given key.
	 *
	 * @param string $paramName Name of the param you're looking for.
	 *
	 * @return string Value for the given param name or null if not found.
	 */
	public function getParam($paramName) {
		if (array_key_exists($paramName, $this->params)) {
			return $this->params[$paramName];
		}
		return null;
	}

}
?>
