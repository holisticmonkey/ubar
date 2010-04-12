<?php
/**
 * Class definition for ActionMapper
 * @package		core
 */

/**
 * An encapsulation of your action definitions
 *
 * This class encapsulates all action definitions, templates, and global
 * results as found in the action config, (ubar.xml). It is restricted to
 * parsing the xml and looking up requsted values. *
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 *
 * @todo Determine how to load directly into classes instead of walking the
 * XML tree.
 */
class ActionMapper {

	/**
	 * @var string The default action name to lookup when no action was found
	 * in the URL.
	 */
	private $defaultActionName;

	/**
	 * @var string The action to use when no action class is specified in your
	 * action definition. This class is where you would locate functionality
	 * you want in all templates and views. It should always return success.
	 *
	 * This replaces DummyAction if defined.
	 *
	 * @see DummyAction
	 * @see GlobalConstants::SUCCESS
	 */
	private $dummyActionPath;

	/**
	 * @var array A collection of action XML objects.
	 */
	private $actions;

	/**
	 * @var array A collection of global result XML objects.
	 */
	private $globalResults;

	/**
	 * @var array A collection of permission XML objects.
	 */
	private $permissionGroups;

	/**
	 * @var array A collection of template XML objects.
	 */
	private $templates;

	/**
	 * Constructor for all definitions: actions, default action, global
	 * results, templates, etc.
	 *
	 * @param string Path to ubar.xml file.
	 *
	 * @todo Make parsing failures be more graceful and provide more meaninful feedback.
	 */
	public function __construct($file) {
		// convert xml of action definitions to an xml object
		libxml_clear_errors();
		$actionDefsXML = simplexml_load_file($file, "SimpleXMLElement", LIBXML_DTDVALID);
		if (libxml_get_last_error()) {
			throw new Exception('Error validating / loading XML');
		}

		// get the name of the default action
		$this->defaultActionName = (string) $actionDefsXML->defaultAction['name'];

		// get the name of the default action
		if(!is_null($actionDefsXML->dummyAction['path'])) {
			$this->dummyActionPath = FileUtils::dotToPath((string) $actionDefsXML->dummyAction['path']);
		}

		// assign actions as a local variable
		$this->actions = $actionDefsXML->actions->action;

		// assign results as a local variable
		$this->globalResults = $actionDefsXML->globalResults->result;

		// assign permission groups as a local variable
		$this->permissionGroups = $actionDefsXML->permissionGroups;

		// assign permission groups as a local variable
		$this->templates = $actionDefsXML->templates->template;
	}

	/**
	 * Get the ActionDef for the given action name.
	 *
	 * @param string $actionName The name of the action you're looking for.
	 *
	 * @return class The action definition associated with the given name.
	 *
	 * @throws Throws and error when no definition was found with the given name.
	 *
	 * @todo Throw a more specific exception.
	 */
	public function getAction($actionName) {
		foreach ($this->actions as $action) {
			if ((string) $action['name'] == $actionName) {
				return new ActionDef($action);
			}
		}

		//throw new ActionNotDefinedException($actionString);
		throw new Exception("No action definition was found with the name \"" . $actionName . "\".");
	}

	/**
	 * Get the TemplateDef for the given template name.
	 *
	 * @param string $templateName Name of the template you're looking for.
	 *
	 * @return class The template definition associated with the given name
	 * or null if not found.
	 *
	 * @todo Improve error messages such that the path resolution is more clear.
	 */
	public function getTemplate($templateName) {
		$returnTemplate = null;
		foreach ($this->templates as $template) {
			if ((string) $template['name'] == $templateName) {
				$templateDef = new TemplateDef($template);

				// if extends other template, use that path
				if (!is_null($template['extends'])) {
					$extendedTemplate = $this->getTemplate($template['extends']);

					// merge defs
					$templateDef->setPath($extendedTemplate->getPath());
					foreach ($extendedTemplate->getParams() as $name => $value) {
						$templateDef->addParam($name, $value);
					}
				}
				return $templateDef;
			}
		}
		return null;
	}

	/**
	 * Get the default action definition. This is used when no action string
	 * or file reference was found in the URL. For example,
	 * "http://www.example.com/".
	 *
	 * @return class The default action definition.
	 *
	 * @see Dispatcher::dispatch()
	 */
	public function getDefaultAction() {
		return $this->getAction($this->defaultActionName);
	}

	/**
	 * Gets the path to the action that overrides DummyAction
	 *
	 * @return string Path to overriding dummy action
	 */
	public function getDummyActionPath() {
		return $this->dummyActionPath;
	}

	/**
	 * Get the global result with the given name.
	 *
	 * NOTE: This is only used if the result was not found on the action
	 * definition.
	 *
	 * @param string $name Name of the result you're looking for.
	 *
	 * @return class The result associated with the given name.
	 *
	 * @see Dispatcher::dispatch()
	 */
	public function getGlobalResult($name) {
		// find result object
		foreach ($this->globalResults as $result) {
			if ((string) $result['name'] == $name) {
				return new Result($result);
			}
		}
		return null;
	}
}
?>
