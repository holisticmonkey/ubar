<?php
/**
 * Class definition for MessageFormat
 * @package		core
 */

/**
 * Message formatting utilities
 *
 * The class, MessageFormat, performs simple expression processing on message
 * bundle messages based on MessageFormat syntax.
 *
 * RULES:
 * <ul>
 * <li>{0} replace with first element in arguments</li>
 * <li>{asdf} replace with whatever element of arguments that has 'asdf' for
 * key iin argument array</li>
 * <li>{0,number,integer} - locale specific number formatting, in this case to
 * be processed as integer</li>
 * <li>{0,choice,0#dogs|1#dog|1<dogs} - do test on argument 0 to determine
 * which of the choices to fall through to</li>
 * </ul>
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 *
 * @todo Complete data formatting such as duration and verbose
 * @todo Complete math evaluation.
 * @todo Determine what to dowith choice conditions that are not satisfied.
 * @todo Cache results if same thing is called a number of times... though
 * might be best to do outside of this class.
 */
class MessageFormat {

	/**
	 * Regular expression to get smallest message format directive that
	 * doesn't contain another directive. Used iteratively to allow for nested
	 * directives.
	 *
	 * Modifiers: multiline, extra analysis, utf-8, ungreedy
	 *
	 * Example: {0,choice,0#dogs|1#dog|1<dogs}
	 */
	const REPLACE_REGEX = '/{([^{,]+)(,[^{]*){0,1}}/mSuU';

	/**
	 * Regular expression to split choice instructions into component parts:
	 * compare value, comparator, resulting condition.
	 *
	 * Modifiers: multiline, extra analysis, utf-8
	 *
	 * Example: 0#dogs|1#dog|1<dogs
	 */
	const CHOICE_REGEX = '/([^#><\|]+)([#><]{1})([^\|]*)/mSu';

	/**
	 * A marker for the start of an invalid expression. Used to prevent further
	 * expression evaluation of a known invalid expression.
	 *
	 * @see MessageFormat::ignore()
	 */
	const MSG_IGNORE_START = 'UBAR_MARKER_';

	/**
	 * A marker for the end of an invalid expression. Used to prevent further
	 * expression evaluation of a known invalid expression.
	 *
	 * @see MessageFormat::ignore()
	 */
	const MSG_IGNORE_END = '_UBAR_MARKER';

	/**
	 * Format a message using message formatting rules, the provided
	 * arguments and the provided locale.
	 *
	 * The locale influences numeric formatting, time formatting and monetary
	 * formatting. Note that this has been disabled until test servers are at
	 * least PHP 5.3.
	 *
	 * @param string $string String to format.
	 * @param array $arguments Arguments used to format or substitute into
	 * message.
	 * @param class $locale Overriding locale object.
	 *
	 * @return string Formatted string.
	 */
	public static function get($string, array $arguments = array(), $locale = null) {
		try {

			// array of directives to ignore because no matching argument found
			$ignoreList = array();

			// while you find something that looks like a directive, try to process it
			while(preg_match(self::REPLACE_REGEX, $string, $match)) {
				// get the key which should match an argument
				$key = trim($match[1]);
				// value from arguments matching key
				$value = NULL;
				// directive instructions - math, choice, number formatting, etc
				$instructions = NULL;

				// key not found in arguments
				if(!array_key_exists($key, $arguments)) {
					// replace unrecognized directive with marker so the while loop doesn't get caught on it again<br />
					// TODO: determine if better way to handle this
					// TODO: log if in dev mode
					self::ignore($match[0], $ignoreList, $string);
					continue;
				} else {
					$value = $arguments[$key];
				}

				// get instruction portion of directive - may be empty
				if(isset($match[2])) {
					$instructions = trim(substr($match[2], 1));
				}

				// if empty, just replace value
				if(Str::nullOrEmpty($instructions)) {
					$string = str_replace($match[0], $value, $string);
				// else, process the instructions using the provided value
				} else {
					$processedValue = self::processValue($value, $instructions, $locale);
					if(!is_null($processedValue)) {
						$string = str_replace($match[0], $processedValue, $string);
					// if the value could not be processed, add to the ignore list
					} else {
						self::ignore($match[0], $ignoreList, $string);
					}
				}
			}

			// if any replace markers for directives with bad keys, put them back now
			foreach($ignoreList as $key => $value) {
				// NOTE: since expression language creates parse errors in xhtml and may otherwise impact markup, escaping characters in string.
				$string = str_replace($key, htmlspecialchars($value), $string);
			}

			// return string now that all directives with associated arguments are replaced
			// TODO: in dev mode, check that there weren't any missed directives because no argument found to match
		} catch (Exception $e) {
			// TODO: determine what expected errors can be caught and handle appropriately
			// something unknown went wrong, make xthml safe since the expression language format will cause xml parse errors
			if(DEV_MODE) {
				throw $e;
			}
			$string = htmlspecialchars($string);
		}
		return $string;
	}

	/**
	 * Ignore an invalid expression. This method substitutes an invalid
	 * expression with a reasonably unique identifier, forestalling further
	 * expression parsing. It is later substited back into the message having
	 * escaped html chars.
	 *
	 * @param string $invalidExpression Invalid expression or expression
	 * without a matching argument.
	 * @param array $ignoreList List of ignored expressions.
	 * @param string $string Message to be formatted.
	 */
	private static function ignore($invalidExpression, &$ignoreList, &$string) {
		$replaceMarker = self::MSG_IGNORE_START . count($ignoreList) . self::MSG_IGNORE_END;
		$string = str_replace($invalidExpression, $replaceMarker, $string);
		$ignoreList[$replaceMarker] = $invalidExpression;
	}

	/**
	 * Process a single set of instructions with the given value and locale.
	 *
	 * @param mixed $value Value to substitute in or use for expression
	 * evaluation.
	 * @param string $instructions Instructions to evaluate with given value.
	 * @param class @locale Overriding locale used is number, monetary, and
	 * date time formatting. Note that this is currently disabled.
	 *
	 * @return string Processed instruction with value.
	 *
	 */
	private static function processValue($value, $instructions, $locale = null) {
		$returnString = NULL;
		$type = NULL;
		$modifiers = NULL;

		$end = stripos($instructions, ',');
		// if there's no comma, treat the whole string as the type
		if(!$end) {
			$type = $instructions;
		// if there's a comma, there's modifiers on the type such as date format, choice instructions, etc
		} else {
			$type = substr($instructions, 0, $end);
			$modifierStart = $end + 1;
			$modifiers = substr($instructions, $modifierStart);
		}

		// format value based on instructions - may convert to locale formatted number, date, or do basic string logic like plurality
		switch ($type) {

		case "number":
			// TODO: do a switch on modifier for float, decimal, money, etc
				// use money_format() for formatting money in the Str class
			if($modifiers == "integer") {
				$value = floor($value);
			}
			$returnString = Str::formatNumber($value, null, $locale);
			break;

		case "choice":
			// TODO: support boolean comparison on each choice
			// TODO: warning if doing numeric operation on something no numeric when in dev mode
			// TODO: figure out how do do ranges and more complex conditions - is this accomplished by most difficult match with < and >?

			// split choice conditions into component parts
			preg_match_all(self::CHOICE_REGEX, $modifiers, $matches);
			// holders of max and min values in > < comparisons so that the most extreme matches win if more than one
			$compositeMin = NULL;
			$compositeMax = NULL;

			foreach($matches[1] as $index => $compareValue) {
				$comparator = $matches[2][$index];
				$result = $matches[3][$index];
				switch($comparator) {

				// equality
				case "#":
					if($compareValue == $value) {
						$returnString = $result;
					}
					// equality is the trump card and wins over > <
					break;

				// value is less than compareValue
				case ">":
					if($compareValue > $value) {
						// make sure least $compareValue that is greater than $value
						if(is_null($compositeMin) || $compareValue < $compositeMin) {
							$returnString = $result;
							$compositeMin = $compareValue;
						}
					}
					break;

				// value greater than compareValue
				case "<":
					if($compareValue < $value) {
						// make sure max $compareValue that is less than than $value
						if(is_null($compositeMax) || $compareValue > $compositeMax) {
							$returnString = $result;
							$compositeMax = $compareValue;
						}
					}
					break;

				// not yet suppoprted comparator
				default:
					if(DEV_MODE) {
						throw new Exception("unsupported comparator \"" . $comparator . "\".");
					}
					break;
				}
			}
			break;

		// do date formatting. note that this should be locale specific
		case "date":
			// if not timestamp, try to make one
			$date = $value;
			if(!is_int($value)) {
				$date = strtotime($value);
				if($date == FALSE && DEV_MODE) {
					throw new Exception("Unable to convert $value into a date");
				}
			}
			// TODO: handle modifiers of "duration", "relative" (to now),"short", etc
			switch($modifiers) {
				case "datetime":
					$returnString = strftime('%c', $date);
					break;
				case "duration":
					// convert to duration like "5 weeks, 3 days ago" or "68 seconds from now"
					//TODO: implement duration formatting
					$returnString = $date;
					break;
				case "verbose":
					$returnString = strftime('%B %d, %Y', $date);
					break;
				case "normal":
					// just fall through to default
				default:
					// do standard date format for current timezone
					// TODO: use %c if they put a time
					$returnString = strftime('%x', $date);
					break;
			}
			break;

		// do mathmatical operation on the given value. {0, math, *5} multiplies {0} by 5
		case "math":
			// TODO: support math functions that aren't described by arithmetic operators such as min, max, floor, ceiling
			// currently allowed tokens to be found in modifier string. note that open and close tags are required to tokenize string properly
			$allowedTokens = array(T_OPEN_TAG, T_CLOSE_TAG, T_LNUMBER, T_WHITESPACE);

			// get tokens from modifier string
			$tokens = token_get_all('<?php ' . $modifiers . ' ?>');

			// flag for whether the modifier string is safe to eval
			$safe = TRUE;

			// make sure there's no risky code in the string
			foreach($tokens as $token) {
				// if this is a token and it's not in the allowed list, throw a warning
				if(is_array($token) && !in_array($token[0], $allowedTokens)) {
					trigger_error("Illegal token found in mathmatical directive. Offending token \"" . $token[1] . "\" was a " . token_name($token[0]) . " in the directive \"$modifiers\".", E_USER_WARNING);
					$safe = FALSE;
				}
			}

			// only evaluate if found to be "safe". otherwise returns null and directive gets ignored
			if($safe) {
				eval("\$returnString = $value $modifiers;");
			}
			break;

		default:
			// TODO: only die in dev mode
			throw new Exception("unsupported directive type \"" . $type . "\".");
			break;
		}
		return $returnString;
	}
}
?>