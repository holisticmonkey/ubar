<?
class ObjectUtils {

	public static $valid_boolean = array("true", "false", "1", "0", 1, 0);
	
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
	
	public static function isNull($value) {
		if(!isset($value) || is_null($value) || $value === NULL) {
			return TRUE;
		}
		return FALSE;
	}
}
?>