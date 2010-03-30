<?php
/**
 * Class definition for ReCaptchaResponse
 * @package		core
 */

/**
 * Captcha response encapsulation
 *
 * The class, Message, is a container for captcha check response.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 *
 * @see ReCaptcha::recaptcha_check_answer()
 */
class ReCaptchaResponse {

	/**
	 * @var boolean Is the response valid.
	 */
	public $is_valid;

	/**
	 * @var boolean Error message or key if any.
	 */
	public $error;
}
?>