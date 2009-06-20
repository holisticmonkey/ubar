<?
class Properties {
	// complate path to propoerty file including file name
	private $file;

	// string containing property contents
	private $properties = array();

	// test if a commented out line

	const PROP_COMMENT_REGEX = '/^\s#.*$/mSu';

	// flag for whether in dev mode, must be set in construct
	private $devMode = true;

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

	private function ingestLine($line, $lineNum) {
		// if first non whitespace char is #, skip
		preg_match(self::PROP_COMMENT_REGEX, $line, $matches);
		if (isset($matches[0])) {
			return;
		}
		// get first index of '=', if -1, skip
		$equalsPos = strpos($line, "=");
		if($equalsPos === false) {
			return;
		}

		$key = trim(substr($line, 0, $equalsPos));
		// preserve whitespace of value until retrieved
		$value = substr($line, $equalsPos + 1);
		// remove newlines
		$value = rtrim($value, "\r\n");

		// if exists, warning that duplicate key found for Foo on line N
		if(isset($this->properties[$key])) {
			throw new Exception("Duplicate key \"$key\" was was found on line $lineNum of the file \"" . $this->file . "\".");
		}

		// else, create entry with given key and value (minus optional first white space char)
		$this->properties[$key] = $value;
	}

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