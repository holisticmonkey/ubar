<?
/*
Simple expression processing on message bundle messages based on OGNL syntax.
*/
class OGNL {
/* 
RULES:
{0} replace with first element in arguments
{asdf} replace with whatever element of arguments that has 'asdf' for key iin argument array
{0,number,integer} - locale specific number formatting, in this case to be processed as integer
{0,choice,0#dogs|1#dog|1<dogs} - do test on argument 0 to determine which of the choices to fall through to
*/
//TODO: complete date formatting
//TODO: complete math stuff
//TODO: support argument that's not an array
//TODO: determine what happens when a choice condition is not satisfied
//TODO: add logging
//TODO: determine what happens when a nested directive fails to be substituted because no matching argument found
//TODO: determine what should happen when string is empty
//TODO: cache results in case same thing is being called a bunch of times - example, lookup for link lable displayed on every line of a record set
	
	// multiline, extra analysis, utf-8, ungreedy
	// regex to get smallest ognl directive match that doesn't contain another directive - used iteratively to allow for nested directives
		// ex. {0,choice,0#dogs|1#dog|1<dogs}
	const replaceRegex = '/{([^{,]+)(,[^{]*){0,1}}/mSuU';
	
	// regex to split choice instructions into component parts - compare value, comparator, resulting condition
		// ex. 0#dogs|1#dog|1<dogs
	const choiceRegex = '/([^#><\|]+)([#><]{1})([^\|]*)/mSu';

	/*
	function to process simple expression language in resource strings
	*/
	public static function get($string, $arguments = array()) {
		try {
			// if argument is not array, wrap in array
			if(!is_array($arguments)) {
				$arguments = array($arguments);
			}
			
			// array of directives to ignore because no matching argument found
			$ignoreList = array();
			
			// while you find something that looks like a directive, try to process it
			while(preg_match(self::replaceRegex, $string, $match)) {
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
					$processedValue = self::processValue($value, $instructions);
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
			$string = htmlspecialchars($string);
		}
		return $string;
	}
	
	// ignore a directive because it was invalid or does not have a matching argument
	private static function ignore($invalidExpression, &$ignoreList, &$string) {
		$replaceMarker = 'OGNL_MARKER_' . count($ignoreList) .  '_OGNL_MARKER';
		$string = str_replace($invalidExpression, $replaceMarker, $string);
		$ignoreList[$replaceMarker] = $invalidExpression;
	}
	
	// try to evaluate expression of directive
	private static function processValue($value, $instructions) {
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
			$returnString = Str::strToInt($value);
			break;
			
		case "choice":
			// TODO: support boolean comparison on each choice
			// TODO: warning if doing numeric operation on something no numeric when in dev mode
			// TODO: figure out how do do ranges and more complex conditions - is this accomplished by most difficult match with < and >?
			
			// split choice conditions into component parts
			preg_match_all(self::choiceRegex, $modifiers, $matches);
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
					continue;
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
					// TODO: only die in dev mode
					die("unsupported comparator \"" . $comparator . "\".");
					continue;
					break;
				}
			}
			break;
			
		// do date formatting. note that this should be locale specific
		case "date":
			// if not timestamp, try to make one
			if(!is_int($value)) {
				$value = strtotime($value);
			}
			// TODO: handle modifiers of "duration", "relative" (to now), "format" (with a provided format string), "verbose", "short", etc
			switch($modifiers) {
			case "duration":
				// convert to duration like "5 weeks, 3 days ago" or "68 seconds from now"
			case "verbose":
				// 
			case "normal":
				// just fall through to default
			default:
				// do standard date format for current timezone
				$returnString = strftime('%c', $value);
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
			die("unsupported directive type \"" . $type . "\".");
			continue;
			break;
		}
		return $returnString;
	}
}
?>