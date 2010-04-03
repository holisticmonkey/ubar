<?php
/**
 * Class definition for ObjectUtils
 * @package		core
 */

/**
 * Object utilities
 *
 * The class, ObjectUtils, is a collection of convenience methods for objects.
 * It includes convenience methods not found natively in PHP.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 */
class ObjectUtils {

	/**
	 * @var array Valid boolean representations. Used in toBoolean()
	 * conversion.
	 *
	 * @see ObjectUtils::toBoolean()
	 */
	public static $valid_boolean = array("true", "false", "1", "0", 1, 0);


	/**
	 * Convert a value to boolean if possible.
	 *
	 * @param mixed $value Value to attempt to convert to boolean.
	 *
	 * @return boolean Boolean value of argument.
	 *
	 * @throws Exception when value cannot be converted to boolean.
	 */
	public static function toBoolean($value) {
		// if already a boolean, just return it
		if(is_bool($value)) {
			return $value;
		// else see if 0 or 1 - convert to bool
		} elseif(is_int($value) && in_array($value, self::$valid_boolean)) {
			return (bool) $value;
		// else see if string representation of boolean
		} elseif(in_array(strtolower($value), self::$valid_boolean, TRUE)) {
			switch (strtolower($value)) {
			case "true":
			case "1":
				return TRUE;
			case "false":
			case "0":
				return FALSE;
			}
		}
		// could not convert, throw error
		throw new Exception("The value \"" . $value . "\" was unable to be converted to a boolean.");
	}

	/**
	 * More lenient null check.
	 *
	 * @param mixed $value Value to check if set or null.
	 *
	 * @return boolean Whether value is null or unset.
	 */
	public static function isNull($value) {
		if(!isset($value) || is_null($value) || $value === NULL) {
			return TRUE;
		}
		return FALSE;
	}
}
?>