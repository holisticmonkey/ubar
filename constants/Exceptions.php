<?php
/**
 * Class definition for Exceptions
 * @package		core
 */

/**
 * Exception constants
 *
 * Exception type constant mappings. Used for custom error display built
 * into framework
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	constants
 *
 * @todo Determine how to best internationalize error name mapping. Make this be
 * DEFAULT_ERROR_TYPE and have a separate array that's a key mapping that allows
 * you to do a props lookup?
 */
class Exceptions {

	/**
	 * @var array $error_type Associative array of error types to nice names.
	 */
	public static $error_type = array (
		E_ERROR              => 'Error', // Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted.
		E_WARNING            => 'Warning', // Run-time warnings (non-fatal errors). Execution of the script is not halted.
		E_PARSE              => 'Parsing Error', // Compile-time parse errors. Parse errors should only be generated by the parser.
		E_NOTICE             => 'Notice', // Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
		E_CORE_ERROR         => 'Core Error', // Fatal errors that occur during PHP's initial startup. This is like an E_ERROR, except it is generated by the core of PHP. 	since PHP 4
		E_CORE_WARNING       => 'Core Warning', // Warnings (non-fatal errors) that occur during PHP's initial startup. This is like an E_WARNING, except it is generated by the core of PHP. 	since PHP 4
		E_COMPILE_ERROR      => 'Compile Error', // Fatal compile-time errors. This is like an E_ERROR, except it is generated by the Zend Scripting Engine. 	since PHP 4
		E_COMPILE_WARNING    => 'Compile Warning', // Compile-time warnings (non-fatal errors). This is like an E_WARNING, except it is generated by the Zend Scripting Engine. 	since PHP 4
		E_USER_ERROR         => 'User Error', // User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error(). 	since PHP 4
		E_USER_WARNING       => 'User Warning', // User-generated warning message. This is like an E_WARNING, except it is generated in PHP code by using the PHP function trigger_error(). 	since PHP 4
		E_USER_NOTICE        => 'User Notice', // User-generated notice message. This is like an E_NOTICE, except it is generated in PHP code by using the PHP function trigger_error(). 	since PHP 4
		E_STRICT             => 'Runtime Notice', // Run-time notices. Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code. 	since PHP 5
		E_RECOVERABLE_ERROR  => 'Catchable Fatal Error' // Catchable fatal error. It indicates that a probably dangerous error occured, but did not leave the Engine in an unstable state. If the error is not caught by a user defined handle (see also set_error_handler()), the application aborts as it was an E_ERROR.
	);
}
?>