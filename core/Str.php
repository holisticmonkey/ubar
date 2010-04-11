<?php
/**
 * Class definition for Str
 * @package		core
 */

/**
 * String utilities
 *
 * The class, Str, is a collection of convenience methods for strings.
 * It includes more complex formatters or tests than are currently available natively
 * in PHP.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 */
class Str {

	/**
	 * A regular expression pattern for use with escapeRegex()
	 * @see Str::escapeRegex()
	 */
	const REGEX_SPECIAL_CHARS_REGEX = '/(\.|\?|\*|\+|\{|\}|\[|\]|\-|\^|\$|\\\\b)/';

	/**
	 * A convenience method to check if a value is either null or an empty
	 * string.
	 *
	 * @param mixed $var Variable to check if value is set, null, or an empty string.
	 * @param boolean $countWhitespace Whether to count whitespace in empty check, for example, "    " can count as empty. Defaults to false.
	 *
	 * @return boolean Indication of whether the $var argument is null or empty
	 */
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

	/**
	 * Escape a string for use in a regular expression. This is useful when a
	 * block of text is to be used in a regular expression match.
	 *
	 * Special characters are . ? * + {} [] - ^ $ \b \B \< \>
	 *
	 * @param string $string The source string to escape.
	 * @return string An escaped version of the string for use in a regular expression.
	 *
	 * @see Str::REGEX_SPECIAL_CHARS_REGEX
	 */
	public static function escapeRegex($string) {
		return preg_replace(self :: REGEX_SPECIAL_CHARS_REGEX, "\\\\$1", $string);
	}

	/**
	 * Format number for locale specific display and with a given number of decimal places.
	 *
	 * @param integer|double $number The number to be formatted.
	 * @param integer $maxDecimals The maximum number of decimal places to use.
	 * @param class $localeOverride Locale to use for formatting. Uses system default if null.
	 * @return string Formatted number.
	 *
	 * @todo Update to use locale support if supported with currently installed php version.
	 * @todo Allow flag for adaptive decimals (ie 10 and 10.332 could both be with 4 decimals if they don't have more decimals..)
	 *
	 */
	public static function formatNumber($number, $maxDecimals = null, $localeOverride = null) {
		if(is_null($maxDecimals)) {
			$maxDecimals = 4;
		}
		$locale = localeconv();
		/*
		if(isset($localeOverride)) {
			$locale = $localeOverride;
			// capture current locale

			// set to new locale if possible

			// get locale info

			// set back to old locale
		} else {
			$locale = localeconv();
		}
		*/
		//die(print_r($locale));
		$decimals = 0;
		$pos = strripos($number, $locale['decimal_point']);
		if ($pos !== false) {
			$decimals = min(strlen($number) - $pos - 1, $maxDecimals);
		}
		return number_format($number, $decimals, $locale['decimal_point'], $locale['thousands_sep']);
	}

	/**
	 * Sanitize markup in user submitted strings.
	 *
	 * A convenience method to only allow basic formatting markup in user
	 * submitted strings. Further formatting on top of strip_tags() is used due
	 * to the fact that style and javascript is not stripped from the tags with
	 * that method and these properties may be malicious. Also, all anchor tags
	 * are modified to open in a new window and newlines converted to breaks.
	 *
	 * @param string $message The message you want to sanitize.
	 * @return string The sanitized message.
	 *
	 * @todo Add support for alternate implementation of anchor target.
	 * This may be a flag for using original property if present, a new default
	 * or a switch for behavior.
	 * @todo Add support for user defined acceptable tags.
	 * @todo Autolink urls and email addresses?
	 * @todo Auto-obfuscate email addresses to prevent harvesting?
	 * @todo Convert entities that will not work in xhtml such as an e with an accent mark.
	 * @todo Strip unclosed or improperly nested tags.
	 */
	public static function sanitizeHTML($message) {
		// strip slashes if necessary
		if (get_magic_quotes_gpc()) {
			$message = stripslashes($message);
		}

		// strip out non-ascii chars
		$message = self::stripNonASCII($message);

		// strip all but allowed tags
		$message = strip_tags($message, '<a><img><b><strong><i><em><u><ul><ol><li>');
		$tagnames = array (
			'a',
			'img',
			'b',
			'strong',
			'li',
			'em',
			'u',
			'ul',
			'ol',
			'li'
		);

		// explicitly strip out javascript anchor tags
		$message = preg_replace('/<a .*href=\"javascript:.*\".*>(.*)<\/a>/sU', "$1", $message);

		// strip tag properties
		foreach ($tagnames as $tagname) {
			switch ($tagname) {
				case "a" :
					$message = preg_replace('/<a .*href=\"([^>]*)\".*>(.*)<\/a>/sU', "<a href=\"$1\" target=\"_blank\">$2</a>", $message);
					break;
				default :
					$message = preg_replace('/<' . $tagname . '[^>]*>(.*)<\/' . $tagname . '>/sU', "<$tagname>$1</$tagname>", $message);
					break;
			}
		}

		// convert newlines to breaks
		$message = nl2br($message);

		// add slashes back if were automatically added prior to this method call
		if (get_magic_quotes_gpc()) {
			$message = addslashes($message);
		}

		return $message;
	}

	/**
	 * Strip newlines
	 *
	 * A convenience method to only strip newlines and carraige returns. Default
	 * use of rtrim strips more characters than desired.
	 *
	 * @param string $string The string you want to strip newlines from.
	 * @return string The formatted string.
	 *
	 * @see Properties::ingestLine()
	 */
	public static function stripNewLines($string) {
		return rtrim($string, "\r\n");
	}

	public static function stripNonASCII($string) {
		return preg_replace('/[^(\x20-\x7F)]*/','', $string);
	}
}
?>