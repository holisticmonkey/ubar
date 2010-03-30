<?php
/**
 * Class definition for Message
 * @package		core
 */

/**
 * Message encapsulation
 *
 * The class, Message, is a container for messages to be surfaced to the user.
 * It has no knowledge of the original arguments or message key, being the
 * rendered version for display. It does, however, have an optional association
 * with an input field allowing, for example, inline error display.
 *
 * Note that this was originally authored instead of simply using associative
 * arrays so that multiple messages may be associated with the same field.
 * However, it is expected that this class will expose more functionality over
 * time.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	containers
 *
 * @todo Consider adding type or severity.
 */
class Message {

	/**
	 * @var string Name of associated input field, if any.
	 */
	private $fieldName;

	/**
	 * @var string Message contents.
	 */
	private $message;

	/**
	 * Custructor for Message.
	 *
	 * @param string $message The message contents, assembled prior to constucting
	 * this class.
	 * @param string $fieldName The optional input field associated with this message.
	 */
	public function __construct($message, $fieldName = null) {
		$this->message = $message;
		$this->fieldName = $fieldName;
	}

	/**
	 * Get the field name, if any associated with this message
	 *
	 * @return string Field name associated with message.
	 */
	public function getFieldName() {
		return $this->fieldName;
	}

	/**
		 * Get the message contents. This is typically in the form of errors,
		 * warnings, and notices. Most messages are the result of retrieving a
		 * raw message from a properties file, substituting in arguments and
		 * evaluating expressions and using the resulting string in this class
		 * custructor.
		 *
		 * @return string The message string.
		 */
	public function getMessage() {
		return $this->message;
	}
}
?>