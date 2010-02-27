<?php
class FileUtils {

	static function dotToPath($string) {
		return preg_replace('/\./', '/', $string) . '.php';
	}

	static function classFromFile($fileName) {
		return basename($fileName, ".php");
	}
}
?>
