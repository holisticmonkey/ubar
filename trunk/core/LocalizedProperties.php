<?


// TODO: make a generic property accessor that this is a wrapper for that knows what files to look for

// regex resources for property matching
define('PROP_VALUE_REGEX_PREPEND', '/^\s*');
// NOTE: modifiers are multiline, extra analysis since reused, and utf-8 compatible
// NOTE: only zero or one white spaces after the "=" are stripped from the match so that you can get spaces before your string...
define('PROP_VALUE_REGEX_APPEND', '\s*=\s{0,1}(.*)$/mSu');

class LocalizedProperties {
	// path to translation files - may come from constant or set in instantiation
	private $path;

	// locale to use to find properties file
	private $locale;

	// file to read properties from
	private $properties;

	// default file to get properties from if not found in locale specific
	private $defaultProperties;

	// TODO: handle naming convention for properties files
	// TODO: figure out how override should happen
	private $propertiesPrepender = "resources";

	private $propertiesAppender = ".properties";

	private $isDefault = false;

	// safe check for dev mode
	// TODO: figure out why this doesn't work
	//var $devMode = (defined('DEV_MODE') && DEV_MODE) ? true : false;
	private $devMode = true;

	public function __construct($locale = NULL, $path = NULL) {
		// use properties root if exists and no override specified
		if ((!isset ($path) || is_null($path)) && defined('PROPERTIES_PATH')) {
			$path = PROPERTIES_PATH;
		}

		// verify directory found
		if (!file_exists($path)) {
			throw new Exception("Path \" $path \" to properties files does not exist. File: " . __FILE__ . " on line: " . __LINE__);
		}

		// get locale
		if (isset ($locale) && !is_null($locale)) {
			// TODO: make it not throw an error if pear package not installed for localization
			// TODO: either use php 5.3 or PEAR i18n
			//$this->locale = Locale::parseLocale($locale);
			//die(print_r($this->locale));
		} else
			if (defined(LOCALE)) {
				$this->locale = LOCALE;
			}
		// TODO: check if valid locale

		// make sure that default properties file exists
		$defaultPath = $path . $this->propertiesPrepender . $this->propertiesAppender;
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
			$localizedPath = $path . $this->propertiesPrepender . "_" . $this->locale . $this->propertiesAppender;
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
		// TODO: see http://en.wikipedia.org/wiki/.properties for other rules
		$this->properties = preg_replace(Properties::PROP_COMMENT_REGEX, "", $this->properties);
	}

	/*
	function to get internationalized resource and do simple expression language processing
		$key - key used to retrive message
		$arguments - array of values or single value to use with expression processing
		$strict - whether to throw an error if message not found
	*/
	// TODO: remove strict and do argument inspection to allow array or just a bunch of args
	// TODO: make it so the first argument is inspected and used as argument list if array
	public function get($key, array $arguments = array (), $strict = FALSE) {
		$entry = $this->getSimple($key, $strict);
		if (count($arguments) > 0) {
			return OGNL :: get($entry, $arguments, $this->locale);
		}
		return $entry;
	}

	private function getSimple($key, $strict = FALSE) {
		// if null, empty, or starts with comment sequence  of '#" or '//' - fail
		// if in dev mode, check to see if exists multiple times and fail saying invalid property file
		//use preg_match_all
		// get regular expresion for matching key
		$regex = PROP_VALUE_REGEX_PREPEND . Str :: escapeRegex($key) . PROP_VALUE_REGEX_APPEND;
		// find resource
		preg_match($regex, $this->properties, $matches);
		// if found, return it
		if (isset ($matches[1])) {
			return $matches[1];
			// not found, if not default, try to find in default resource
		} else
			if (!$this->isDefault) {
				// TODO: if devmode, log that main not found and looking for default
				// find default
				preg_match($regex, $this->defaultProperties, $matches);
				// if found, return it
				if (isset ($matches[1])) {
					return $matches[1];
				}
			}
		// TODO: if devmode, log that no entry found for given key
		if ($strict) {
			throw new Exception("The key \"" . $key . "\" was not found.");
		} else {
			return $key;
		}
	}
}
?>