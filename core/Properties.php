<?php
/**
 * Class definition for Properties
 * @package core
 */

/**
 * Interface to a properties file.
 *
 * The class, Properties, retrieves messages for a given key in a properties
 * file.
 *
 * EXAMPLE:
 * #this is a comment and will be ignored
 * foo.bar.key = This message can be retrieved by calling get("foo.bar.key")
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 *
 * @todo Consider cacheing so that you store the results in a php file rather
 * than parsing a file on each load. It may not be a significant improvement.
 * @todo Make use of dev mode instead of or in addition to error suppression.
 * @todo Error out on invalid lines.
 */
class Properties {

	/**
	 * @var string Path to property file.
	 */
	private $file;

	// string containing property contents
	/**
	 * @var array Associative array of key and message.
	 */
	private $properties = array();

	/**
	 * Regular expression to strip comments out of lines before processing.
	 *
	 * NOTE: (*ANYCRLF) is not required since this is used on a single line,
	 * not across lines.
	 *
	 * EXAMPLE:
	 * # comment one
	 * !comment two
	 * asdf # this is not a comment
	 *
	 * OPTIONS: extra analysis, utf-8
	 */
	const PROP_COMMENT_REGEX = '/^\s*(#|!).*$/Su';

	/**
	 * Construct an interface to a given properties file.
	 *
	 * @param string $file File to retrieve properties from.
	 * @param boolean $suppressException Flag for whether to throw an error
	 * if the file is unable to be loaded.
	 */
	public function __construct($file = NULL, $suppressException = false) {
		// verify directory found
		if (!file_exists($file)) {
			if (!$suppressException) {
				// TODO: turn into named exception and localize
				$errorMsg = "Path \"$file\" to properties file does not exist. File: " . __FILE__ . " on line: " . __LINE__;
				throw new Exception($errorMsg);
			}
		} else {

			$this->file = $file;
			// load contents
			$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			// Loop through our array and convert to properties entries
			foreach ($lines as $lineNum => $line) {
			    $this->ingestLine($line, $lineNum);
			}
		}
	}

	/**
	 * Ingest a single line of the properties file. This skips comments,
	 * skips unparseable lines, trims whitespace around keys and
	 * checks for duplicate keys before pushing into local array of properties.
	 *
	 * @param string $line Line to be processed.
	 * @param integer $lineNum Line number being processed. Only used in error
	 * reporting.
	 *
	 * @throws Exception when duplicate keys were found.
	 *
	 * @todo Better handle unparseable lines.
	 */
	private function ingestLine($line, $lineNum) {
		// strip out comments
		$line = preg_replace(self::PROP_COMMENT_REGEX, "", $line);

		// get first index of '=', if -1, skip
		$equalsPos = strpos($line, "=");
		if($equalsPos === false) {
			return;
		}

		$key = trim(substr($line, 0, $equalsPos));
		// preserve whitespace of value until retrieved
		$value = substr($line, $equalsPos + 1);
		// remove newlines
		$value = Str::stripNewlines($value);

		// if exists, warning that duplicate key found for Foo on line N
		if(isset($this->properties[$key])) {
			throw new Exception("Duplicate key \"$key\" was was found on line $lineNum of the file \"" . $this->file . "\".");
		}

		// else, create entry with given key and value (minus optional first white space char)
		$this->properties[$key] = $value;
	}

	/**
	 * Get message associated with key. Allow default value if not found and
	 * allow preservation of whitespace.
	 *
	 * @param string $key Key to message you're looking for.
	 * @param string $default Default value if key was not found.
	 * @param boolean $preserveExtraSpace Flag indicating whether ot preserve
	 * whitespace or trim value.
	 *
	 * @return string Message value or default value.
	 *
	 * @throws Exception if no default and key not found.
	 *
	 * @todo Determine a way for the default to be explicitly null.
	 * @todo Consider stripping first space even if flag for preserving space
	 * is set to true as standard practice in properties files is for there to
	 * be at least one space after the equals.
	 */
	public function get($key, $default = NULL, $preserveExtraSpace = FALSE) {
		// if found, return it
		if (array_key_exists($key, $this->properties)) {
			$value = $this->properties[$key];
			if ($preserveExtraSpace) {
				// TODO: consider stripping first space if present for consistency
				return $value;
			}
			return trim($value);

			// not found, throw exception
		} else {
			if (ObjectUtils :: isNull($default)) {
				throw new Exception("No property was found with the key \"" . $key . "\" in the file \"" . $this->file . "\".");
			} else {
				return $default;
			}
		}
	}

	/**
	 * Get boolean value for key. This is the same as get() but attempts to
	 * cast the value too boolean for convenience.
	 *
	 * @param string $key Key to message you're looking for.
	 * @param boolean $default Default value if key was not found.
	 *
	 * @throws Exception if the property or default could not be cast to
	 * boolean.
	 */
	public function getBool($key, $default = NULL) {
		$value = self :: get($key, $default, FALSE);
		try {
			return ObjectUtils :: toBoolean($value);
		} catch (Exception $e) {
			throw new Exception("The property found, " . $value . ", with the key \"" . $key . "\" could not be converted to a boolean value in the file \"" . $this->file . "\".");
		}
	}
}
?>