<?php
/**
 * Class definition for DummyAction
 * @package		core
 */

/**
 * A fake action for use when no action is necessary to display a view.
 *
 * This class is used when no processing is necessary for a given view.
 * The action is still required so that functionality from the Action class
 * can still be exposed to the view.
 *
 * All this class does is return GlobalConstants::SUCCESS when executed.
 *
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 */
class DummyAction extends Action {

	/**
	 * Always return GlobalConstants::SUCCESS when executed.
	 *
	 * @return string The success string.
	 */
	public function executeInner() {
		return GlobalConstants :: SUCCESS;
	}
}
?>