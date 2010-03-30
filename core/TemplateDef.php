<?php
// TODO: make this less generic? intead of just params, have specific things?
/**
 * Class definition for TemplateDef
 * @package		core
 */

/**
 * Container for template definitions.
 *
 * This class encapsulates templated definitions as found in the action config,
 * (ubar.xml). It primarily acts as an interface to the values and does some
 * minor conversions.
 *
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 *
 * @todo Consider adding specific properties in the xml and here for common
 * properties like title, section, subsection...
 * @todo Consider pushing template merge functionality here.
 */
class TemplateDef {

	/**
	 * @var string Path to template file.
	 */
	private $path;

	/**
	 * @var array $params Collection of params for this definition.
	 */
	private $params = array();

	/**
	 * Construct the action definition from the contents of the ubar.xml file.
	 *
	 * @param class $xmlDef - XML representation of an template definition.
	 *
	 * @see ActionMapper::getTemplate()
	 */
	public function __construct($xmlDef) {
		if (!is_null($xmlDef->attributes()->path)) {
			$pathString = (string) $xmlDef->attributes()->path;
			$this->path = FileUtils :: dotToPath($pathString);
		}
		foreach ($xmlDef->param as $param) {
			$attribs = $param->attributes();
			$name = (string) $attribs->name;
			$value = (string) $attribs->value;
			$this->addParam($name, $value);
		}
	}

	/**
	 * Set path to template file. This is used when this template definition
	 * does not have a path but the definition that it extends does.
	 *
	 * NOTE: This is only public so that ActionMapper::getTemplate() can
	 * merge the definitions. Do not call directly.
	 *
	 * @param string $path Path to the template file.
	 *
	 * @see ActionMapper::getTemplate()
	 */
	public function setPath($path) {
		if(is_null($this->path)) {
			$this->path = $path;
		}
	}

	/**
	 * Add a parameter to the definition. This is used to merge parameteres
	 * from definitions that this extends. It will not override existing values
	 * if a param with that key already exists.
	 *
	 * NOTE: This is only public so that ActionMapper::getTemplate() can
	 * merge the definitions. Do not call directly.
	 *
	 * @param string $name Name of the param.
	 * @param string $value Value associated with the param.
	 *
	 * @see ActionMapper::getTemplate()
	 */
	public function addParam($name, $value) {
		if(!array_key_exists($name, $this->params)) {
			$this->params[$name] = $value;
		}
	}

	/**
	 * Get path to template file associated with this definition.
	 *
	 * @return string Path to template file.
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get parameters associated with this definition.
	 *
	 * @return array List of params.
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Get a param for the given key.
	 *
	 * @param string $paramName Name of the param you're looking for.
	 *
	 * @return string Value associated with the given key.
	 */
	public function getParam($paramName) {
		if(array_key_exists($paramName, $this->params)) {
			return $this->params[$paramName];
		}
		return null;
	}

	/**
	 * Get page section, if defined.
	 *
	 * @return string Section name if defined.
	 */
	public function getSection() {
		return $this->getParam("section");
	}

	/**
	 * Get page sub-section, if defined.
	 *
	 * @return string Sub-section name if defined.
	 */
	public function getSubSection() {
		return $this->getParam("subSection");
	}

	/**
	 * Get page title, if defined.
	 *
	 * @return string Title name if defined.
	 */
	public function getTitle() {
		return $this->getParam("title");
	}

	/**
	 * Get page title key, if defined. This is used to retrieve the title from
	 * the LocalizedProperties instance.
	 *
	 * @return string Title key if defined.
	 */
	public function getTitleKey() {
		return $this->getParam("titleKey");
	}
}
?>
