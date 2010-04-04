<?php
/**
 * Class definition for LocalizedProperties
 * @package core
 */

/**
 * Interface to a locale specific properties.
 *
 * The class, LocalizedProperties, gets a locale specific properties file and
 * merges it with a default properties file. Properties may be retrieved with
 * expression evaluation and argument substitution.
 *
 * Prior to the 1.0 release of this framework, this will also support locale
 * specific number, money and time formatting.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 *
 * @todo Consider caching.
 * @todo see http://en.wikipedia.org/wiki/.properties for other rules for
 * compatability with standards.
 */
class LocalizedProperties {

	/**
	 * @var string Path to localized properties files.
	 */
	private $path;

	/**
	 * @var class Locale to use for primary properties file lookup and
	 * money/numeric/time formatting.
	 */
	private $locale;

	/**
	 * @var string Contents of file to read properties from.
	 */
	private $properties;

	/**
	 * @var string Contents of default properties file if key not found in $properties.
	 * @see LocalizedProperties::$properties
	 */
	private $defaultProperties;


	/**
	 * Appender for all properties file names.
	 */
	const PROPERTIES_APPENDER = ".properties";

	/**
	 * A prepend string for the dynamic property regular expression.
	 *
	 * NOTE: Added (*ANYCRLF) so that CR/LF/CRLF/EOF all match the $ in
	 * PROP_VALUE_REGEX_APPEND below. Otherwise it would only match LF/EOF.
	 */
	const PROP_VALUE_REGEX_PREPEND = '/(*ANYCRLF)^\s*';

	/**
	 * Appender for regular expression to extract the value from a property line.
	 * The whole regex should match start of line, any amount of whitespace
	 * before and after the search key, zero or one spaces after the "=" sign,
	 * any chars followed by an optional CR and either a newline or end of
	 * file. Note that the CR is present, optionally, due to $ matching only
	 * a line feed or end of file, CR would be captured otherwise.
	 *
	 * Modifiers: multiline, extra analysis, utf-8, complain if bad escape
	 *
	 * Example: key = message
	 *
	 * NOTE: only zero or one white spaces after the "=" are stripped from the
	 * match so that you can get spaces before your string...
	 */
	const PROP_VALUE_REGEX_APPEND = '\s*=\s{0,1}(.*)$/mSuX';

	/**
	 * @var boolean $isDefault Flag indicating that the properties file used
	 * is the default properties file.
	 */
	private $isDefault = false;

	/**
	 * Construct a localized properties instance with the given locale and
	 * path. The path is only used as an override and should not be used
	 * regularly.
	 *
	 * @param class $locale Locale to use for properties file lookup and
	 * formatting.
	 * @param string $path Override to path to properties files.
	 */
	public function __construct($locale = NULL, $path = NULL) {
		// use properties root if exists and no override specified
		if ((!isset ($path) || is_null($path)) && defined('PROPERTIES_PATH')) {
			$path = PROPERTIES_PATH;
		}

		// verify directory found
		if (!file_exists($path)) {
			throw new Exception("Path \"$path\" to properties files does not exist. File: " . __FILE__ . " on line: " . __LINE__);
		}

		// get locale
		if (isset ($locale) && !is_null($locale)) {
			// TODO: make it not throw an error if pear package not installed for localization
			// TODO: either use php 5.3 or PEAR i18n
			//$this->locale = Locale::parseLocale($locale);
			$this->locale = $locale;
			//die(print_r($this->locale));
		} else {
			if (defined('LOCALE')) {
				// TODO: convert to Locale instance
				$this->locale = LOCALE;
			}
		}

		// TODO: check if valid locale

		// make sure that default properties file exists
		$defaultPath = $path . PROPERTIES_ROOT . self::PROPERTIES_APPENDER;
		if (!file_exists($defaultPath)) {
			throw new Exception('Default properties file \'' . $defaultPath . '\' does not exist.');
		}

		// if this is the default locale or there was no override, set default as main properties
		if ($this->locale == LOCALE_DEFAULT || Str :: nullOrEmpty($this->locale)) {
			$this->isDefault = true;
			$this->properties = file_get_contents($defaultPath);
			// else, try to get localized properties file
		} else {
			$this->defaultProperties = file_get_contents($defaultPath);
			$localizedPath = $path . PROPERTIES_ROOT . "_" . $this->locale . self::PROPERTIES_APPENDER;
			// if localized file doesn't exist, set default as primary and log that couldn't find localized property file
			if (!file_exists($localizedPath)) {
				//throw new Exception('Properties file \'' . $propertiesPath . '\' does not exist.');
				// log that couldn't find locale specific properties
				$this->properties = $this->defaultProperties;
				// get localized properties file
			} else {
				$this->properties = file_get_contents($localizedPath);
			}
		}
		// strip comments - this is anything where the first non whitespace is #
		$this->properties = preg_replace(Properties::PROP_COMMENT_REGEX, "", $this->properties);
	}

	/**
	 * Get value for for a given key and arguments. This uses message
	 * formatting to substitute arguments or parse simple expressions.
	 *
	 * @param string $key Key to use for message lookup.
	 * @param mixed $arguments Arguments to use for expression evaluation and
	 * substitution.
	 * @param boolean $strict Whether to throw an error when key not found.
	 *
	 * @return string Processed message.
	 */
	public function get($key, $arguments = array (), $strict = FALSE) {
		// convenience, if one arg passed in, need not be array
		if(is_scalar($arguments)) {
			$arguments = array($arguments);
		}
		$entry = $this->getSimple($key, $strict);
		if (count($arguments) > 0) {
			return MessageFormat :: get($entry, $arguments, $this->locale);
		}
		return $entry;
	}

	/**
	 * Get value for a given key but do no expression evaluation.
	 *
	 * @param string $key Key to use for message lookup.
	 * @param boolean $strict Whether to throw an error when key not found.
	 *
	 * @return string Message.
	 *
	 * @throws Exception if key not found and $strict == true.
	 */
	private function getSimple($key, $strict = FALSE) {
		// if null, empty, or starts with comment sequence  of '#" or '//' - fail
		// if in dev mode, check to see if exists multiple times and fail saying invalid property file
		//use preg_match_all
		// get regular expresion for matching key
		$regex = self::PROP_VALUE_REGEX_PREPEND . Str :: escapeRegex($key) . self::PROP_VALUE_REGEX_APPEND;
		// find resource
		preg_match($regex, $this->properties, $matches);
		// if found, return it
		if (isset ($matches[1])) {
			return $matches[1];
			// not found, if not default, try to find in default resource
		} else
			if (!$this->isDefault) {
				preg_match($regex, $this->defaultProperties, $matches);
				// if found, return it
				if (isset ($matches[1])) {
					return $matches[1];
				}
			}
		if ($strict) {
			throw new Exception("The key \"" . $key . "\" was not found.");
		} else {
			return $key;
		}
	}
}
?>