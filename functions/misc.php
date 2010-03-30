<?php
/**
 * Miscelaneous Functions
 *
 * Misc functions that should be moved to more specific collections. As the
 * list of functions is currently very limited, they are being left here.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	functions
 */

/**
 * Magic autoloader override.
 *
 * This is used to autoload classes in known class folders. The global $classes
 * variable contains the list of classes to search.
 *
 * @param string $className Name of the class you're attempting to load.
 * @throws Exception when unable to load a given class.
 *
 * @todo Also look for stuff in the path and pear classes
 * http://php.net/manual/en/language.oop5.autoload.php
 * @todo Switch to using spl autoload so you can register multiple autoloaders
 * and lib implementers can use it
 * see - http://www.php.net/manual/en/function.spl-autoload.php
 *
 */
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

/**
 * Assign folders as search locations for class autoloading.
 *
 * @param string $directory Directory to add to search list.
 * @param boolean $recursive Whether the search directory should be recursively
 * added to search list.
 */
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

/**
 * Custom error handler. For the most part it addresses formatting issues.
 *
 * @param integer $typeNumber Index of error type.
 * @param string $message Error message.
 * @param string $file File path.
 * @param integer $line Line number.
 * @param array $variables Variables in context. Currently not used due to
 * verbosity.
 *
 * @return boolean True, so ask to skip PHP internal error handler.
 *
 * @todo Honor html_errors ini settings using sprintf to substitute into string templates.
 * @todo Make an error object with pertinent settings.
 * @todo If error happens before page is rendered, may get xhtml parse error since not wrapped in document.
 */
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

/**
 * Custom exception handler. For the most part it addresses formatting issues.
 *
 * @param class $e Exception to render
 *
 * @return boolean True, so ask to skip PHP internal error handler.
 *
 * @todo Honor html_errors ini settings using sprintf to substitute into string templates.
 */
function exceptionHandler($e) {
	echo "<div style=\"padding: 20px; margin-bottom: 10px; font-family: monospace; font-size: 16px; border: 2px solid #FF0000; background :#FFCC00; white-space: pre;\">";
	echo '<strong>Uncaught ' . get_class($e) . '</strong> (' . $e->getCode() . ")\n\n";
	echo "<strong>Location</strong>: " . $e->getFile() . " line " . $e->getLine() . "\n";
	echo "<strong> Message</strong>: " . htmlentities($e->getMessage()) . "\n";
	echo "<strong>   Stack</strong>:\n<div style=\"padding: 5px 0px 0px 50px\">" . htmlentities($e->getTraceAsString ()) . "</div>";
	echo "</div>";
}

/**
 * Developers convenience method for printing an object to screen. Only renders
 * if DEV_MODE is on to prevent accidental exposure on production sites.
 *
 * @param class $object Object to render.
 */
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