<?
class Str {

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
		if (is_array($var) && count($var) == 0) {
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

	#convert to html special chars exept for links, images, and basic font modifiers. also convert newlines to breaks
	#function intended for use with user posted copy to allow basic formatting/functionality but also making html safe and allowing display of code
	#note: may also want to auto link any urls and obfuscate all email addresses
	public static function sanitizeHTML($message) {
		if (get_magic_quotes_gpc()) {
			$message = stripslashes($message);
		}
		#special tag html that I want to support ( currently links, images, basic font modifiers )
		$message = preg_replace("/<a href=\"(.*)\".*>(.*)<\/a>/sU", "[::a $1 ::]$2[::/a::]", $message);
		$message = preg_replace("/<img src=\"(.*)\".*>/sU", "[::img $1 ::]", $message);
		$message = preg_replace("/<b>(.*)<\/b>/sU", "[::b::]$1[::/b::]", $message);
		$message = preg_replace("/<strong>(.*)<\/strong>/sU", "[::b::]$1[::/b::]", $message);
		$message = preg_replace("/<i>(.*)<\/i>/sU", "[::i::]$1[::/i::]", $message);
		$message = preg_replace("/<em>(.*)<\/em>/sU", "[::i::]$1[::/i::]", $message);
		$message = preg_replace("/<u>(.*)<\/u>/sU", "[::u::]$1[::/u::]", $message);
		#make html safe and then convert newlines to breaks to preserve message formatting
		$message = nl2br(htmlspecialchars($message));
		#swap back from special tags to actual html tags
		$message = preg_replace("/\[::a\s(.*)\s::\](.*)\[::\/a::\]/sU", "<a href=\"$1\" target=\"_blank\">$2</a>", $message);
		$message = preg_replace("/\[::img\s(.*)\s::\]/sU", "<img src=\"$1\" border=\"0\">", $message);
		$message = preg_replace("/\[::b::\](.*)\[::\/b::\]/sU", "<strong>$1</strong>", $message);
		$message = preg_replace("/\[::i::\](.*)\[::\/i::\]/sU", "<em>$1</em>", $message);
		$message = preg_replace("/\[::u::\](.*)\[::\/u::\]/sU", "<u>$1</u>", $message);
		if (get_magic_quotes_gpc()) {
			$message = addslashes($message);
		}
		return $message;
	}

	// add stripNewlines(), see Properties
}
?>