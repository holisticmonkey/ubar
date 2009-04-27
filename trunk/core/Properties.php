<?
class Properties {
	// complate path to propoerty file including file name
	private $file;
	
	// string containing property contents
	private $properties;
	
	// properties match regular expression
	// NOTE: contains '##PROP_KEY##' that should be replaced with the key prior to use
	const PROP_VALUE_REGEX = '/^\s*##PROP_KEY##\s*=\s*([^\n\r]*)\s*$/mSu';
	// NOTE: preserves whitespace in message except for first one - used when presentation is important
	const PROP_VALUE_REGEX_PESERVE_SPACE = '/^\s*##PROP_KEY##\s*=\s{0,1}([^\n\r]*)$/mSu';
	
	// flag for whether in dev mode, must be set in construct
	private $devMode = true;

	public function __construct($file = NULL) {				
		// verify directory found
		if(!file_exists($file)) {
			// TODO: turn into named exception and localize
			throw new Exception('Path \'' . $file . '\' to properties files does not exist.');
		}

		$this->file = $file;
		// load contents
		$this->properties = file_get_contents($file);
	}

	public function get($key, $default = NULL, $preserveExtraSpace = FALSE) {
		// TODO: if key is null, empty, or starts with comment sequence  of '#" or '//' - fail
		// get regex to use
		$regex = $preserveExtraSpace ? self::PROP_VALUE_REGEX_PESERVE_SPACE : self::PROP_VALUE_REGEX;
		// update regex string to substitute key in
		$regex = str_replace('##PROP_KEY##', Str::escapeRegex($key), $regex);
		// find resource
		preg_match($regex, $this->properties, $matches);
		// if found, return it
		if(isset($matches[1])) {
			return $matches[1];
		// not found, throw exception
		} else {
			if(ObjectUtils::isNull($default)) {
				throw new Exception("No property was found with the key \"" . $key . "\" in the file \"" . $this->file . ".");
			} else {
				return $default;
			}
		}
	}
	
	public function getBool($key, $default = NULL) {
		$value = self::get($key, $default, FALSE);
		try {
			return ObjectUtils::toBoolean($value);
		} catch (Exception $e) {
			throw new Exception("The property found, " . $value . ", with the key \"" . $key . "\" could not be converted to a boolean value in the file \"" . $this->file . ".");
		}
	}
}
?>