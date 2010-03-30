<?php
/**
 * Class definition for FileUtils
 * @package		core
 */

/**
 * File utilities
 *
 * The class, FileUtils, is a collection of convenience methods for files.
 * It currently includes only a few simple functions having to do with paths,
 * however, it is expected to be fleshed out in future iterations of the
 * framework.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 */
class FileUtils {

	/**
	 * Convert a dot path to a system agnostic file path. Typically this is
	 * used for java-like class references like "com.foo.bar.Bang".
	 *
	 * @param string $string Dot concatenated string to be converted.
	 *
	 * @return string Path to file.
	 */
	public static function dotToPath($string) {
		return preg_replace('/\./', '/', $string) . '.php';
	}

	/**
	 * Convert a file path to a classname assuming standard naming conventions.
	 * For instance, /this/is/a/path/to/a/class/named/Bang.php would define a
	 * class "Bang".
	 *
	 * @param string $path Path to get classname from.
	 *
	 * @return string Classname for a file.
	 */
	public static function classFromFile($path) {
		return basename($path, ".php");
	}
}
?>
