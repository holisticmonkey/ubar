<?php
/**
 * Class definition for JSONAction
 * @package		core
 */

/**
 * A special Action used for rendering data in JSON form.
 *
 * This class will be used to convert an object tree into a JSON string and
 * render the result as a string representation. It should be extended by
 * classes that want to render to JSON as it will have helper methods and
 * objects to store and manage JSON contents.
 *
 * NOTE: It will be complete and tested prior to the 1.0 framework release.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 *
 * @todo Add a JSON container.
 * @todo Add methods for adding and working with the json object, see json.org.
 * @todo Implement rendering the JSON container to string.
 */
abstract class JSONAction extends Action {

	public function getJSONString() {
		return "this should render the json object";
	}

	public function executeInner() {
		return GlobalConstants::SUCCESS;
	}
}
?>