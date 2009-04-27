<?
class Str {
	// TODO: make everything static
	public static function nullOrEmpty($var, $countWhitespace = false) {
		// if null or not initialized, return true
		if (!isset ($var) || is_null($var)) {
			return true;
		}
		// if it's a string, check to see if it's empty - use flag to determine if whitespace counts as not empty
		if (is_string($var)) {
			if (!$countWhitespace) {
				$var = trim($var);
			}
			return $var === '';
		}
		// return true if array of size zero
		if(is_array($var) && count($var) == 0) {
			return true;
		}
		return false;
	}

	// TODO: replace anything that is a special char in a regex
	public static function escapeRegex($string) {
		$string = str_replace(".", "\.", $string);
		return $string;
	}

	public static function strToInt($number) {
		$locale = localeconv();
		return number_format($number, 0, $locale['decimal_point'], $locale['thousands_sep']);
	}
}
?>