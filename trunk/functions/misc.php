<?php

// magic auto-loader function that tries to load classes that are in the allowed list
// TODO: allow user to specify an autoload directory
function __autoload($className) {
	global $classes;
	$filename = $className . ".php";
	if (isset ($classes[$filename])) {
		// use require instead of require_once since never get here with this class already defined
		require ($classes[$filename]);
	} else {
		throw new Exception("Failed to autoload class \"$className\".");
		// log that failed to find class through auto-loading
	}
}

// get files allowed to be auto loaded
function getClassPaths($directory, $recursive = FALSE) {
	global $classes;

	$dir = opendir($directory);
	while ($entry = readdir($dir)) {
		if ($entry != "." && $entry != ".." && !(strstr($entry, '.svn') > -1)) {
			$file = $directory . '/' . $entry;
			if (is_dir($file) && $recursive) {
				getClassPaths($file, $recursive);
			} elseif (is_file($file) && (substr($file, strlen($file) - 4, 4) == '.php')) {
				$classes[$entry] = $directory . '\\' . $entry;
			}
		}
	}
}

// TODO: move presentation elsewhere
// TODO: internationalize but have try catches around resource retrieval
// TODO: make an error object with the pertinent properties
// TODO: don't echo, push into a collection of error objects that (remember to use type hinting) that is displayed on an error page
// TODO: figure out how to send to another page when you want to catch all errors before redirecting... can you listen for completion of scripting and
// TODO: if this error happens before a page is rendered, may get xhtml parse error since not wrapped in a document, consider trying to set header to html (if xhtml) in a try catch, prepending with <html><document> if doesn't fail
function errorHandler($typeNumber, $message, $file, $line, $variables) {
	// Error type names array

	// assemble message, converting error number to display name, escaping characters to make xhtml compatible
	$errorMessage = "<strong>" . Exceptions :: $error_type[$typeNumber] . "</strong> -  " . htmlspecialchars($message) . "<br />Line $line in file $file.<br />PHP Version " . PHP_VERSION . "<br />";

	// if variables provided, add them to message
	// NOTE: disabled as too verbose currently
	/*
	if(isset($variables) && !is_null($variables) && count($variables) > 0) {
		// convert variables to human readable format and make xhtml safe
		$errorMessage .= "Variables: " . nl2br(htmlspecialchars(print_r($variables, TRUE)));
	}
	*/

	// put an extra break for visual separation from next error
	$errorMessage .= "<br />";

	// print message
	echo $errorMessage;

	// abort if one of the errors that should cause an abort
	if (in_array($typeNumber, array (
			E_ERROR,
			E_CORE_ERROR,
			E_COMPILE_ERROR,
			E_USER_ERROR
		))) {
		echo "Aborting...<br />";
		exit (1);
	}

	/* Don't execute PHP internal error handler */
	return true;
}

function debug($object, $explode = false) {
	if (DEV_MODE) {
		if ($explode) {
			echo "<pre>";
			print_r($object);
			echo "</pre>";
		} else {
			echo $object . "<br>";
		}
	}
}
?>