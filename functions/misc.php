<?php

// magic auto-loader function that tries to load classes that are in the allowed list
// TODO: allow user to specify an autoload directory
// TODO: also look for stuff in the path and pear classes http://php.net/manual/en/language.oop5.autoload.php
// TODO: switch to using spl autoload so you can register multiple autoloaders and lib implementers can use it
// 		 http://www.php.net/manual/en/function.spl-autoload.php
function __autoload($className) {
	global $classes;
	$filename = $className . ".php";
	if (isset ($classes[$filename])) {
		// use require instead of require_once since never get here with this class already defined
		require($classes[$filename]);
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

// TODO: honor html_errors ini settings, wrap around div statements, have html and non html message versions and use sprintf for args
// TODO: move presentation elsewhere
// TODO: make an error object with the pertinent properties
// TODO: don't echo, push into a collection of error objects that (remember to use type hinting) that is displayed on an error page
// TODO: figure out how to send to another page when you want to catch all errors before redirecting... can you listen for completion of scripting and
// TODO: if this error happens before a page is rendered, may get xhtml parse error since not wrapped in a document, consider trying to set header to html (if xhtml) in a try catch, prepending with <html><document> if doesn't fail
function errorHandler($typeNumber, $message, $file, $line, $variables) {
	echo "<div style=\"padding: 20px; margin-bottom: 10px; font-family: monospace; font-size: 16px; border: 2px solid #FF0000; background :#FFCC00; white-space: pre;\">";

	// assemble message, converting error number to display name, escaping characters to make xhtml compatible
	echo ("<strong>" . Exceptions :: $error_type[$typeNumber] . "</strong> -  " . strip_tags($message) . "\nLine $line in file $file.\nPHP Version " . PHP_VERSION . "\n");

	// if variables provided, add them to message
	// NOTE: disabled as too verbose currently
	/*
	if(isset($variables) && !is_null($variables) && count($variables) > 0) {
		// convert variables to human readable format and make xhtml safe
		$errorMessage .= "Variables: " . nl2br(htmlspecialchars(print_r($variables, TRUE)));
	}
	*/

	echo "</div>";

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

function exceptionHandler($e) {
	echo "<div style=\"padding: 20px; margin-bottom: 10px; font-family: monospace; font-size: 16px; border: 2px solid #FF0000; background :#FFCC00; white-space: pre;\">";
	echo '<strong>Uncaught ' . get_class($e) . '</strong> (' . $e->getCode() . ")\n\n";
	echo "<strong>Location</strong>: " . $e->getFile() . " line " . $e->getLine() . "\n";
	echo "<strong> Message</strong>: " . htmlentities($e->getMessage()) . "\n";
	echo "<strong>   Stack</strong>:\n<div style=\"padding: 5px 0px 0px 50px\">" . htmlentities($e->getTraceAsString ()) . "</div>";
	echo "</div>";
}

function debug($object) {
	if (DEV_MODE) {
		if (is_object($object) || is_array($object)) {
			echo "<pre>";
			print_r($object);
			echo "</pre>";
		} else {
			echo $object . "<br>";
		}
	}
}
?>