<?php

/* NOTE: this db manager is rudimentary at this time but is required for the built in user management.
 * If you use multiple databases or doing extensive db work, you may want to disable this for the time being.
 *
 * Notable features are bootstrapping the database and supporting schema versioning/migration.
*/
// TODO: have DB specific exceptions?
// TODO: have class bootstrap framework specific tables for metainfo storage
class DBManager {

	private $link;

	// TODO: get version from properties
	private $version = 2;

	// 	construct from predefined values if defined
	public function __construct() {
		if (defined("DB_SERVER") && defined("DB_USERNAME") && defined("DB_PASSWORD") && defined("DB_NAME")) {
			$result = $this->connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, true);
			if (!$result) {
				throw new Exception("Unable to connect to the database with the given credentials");
			}
		} else {
			throw new Exception("One or more required fields for connection to a database was not defined.");
		}
	}

	// connect to a database and store the link. returns true if connceted to mysql/db depending on params
	public function connect($server, $user, $pass, $dbName, $createIfNotExists = false) {
		// exit if already connected
		if (!is_null($this->link)) {
			return true;
		}
		$link = mysql_connect($server, $user, $pass);
		if (!$link) {
			throw new Exception("Unable to connect to database: " . mysql_error());
		}

		// select database
		if (!is_null($dbName)) {
			if (!mysql_select_db($dbName)) {
				// if it doesn't exist, create it
				if ($createIfNotExists) {
					mysql_query("CREATE DATABASE " . $dbName);
					if(!mysql_select_db($dbName)) {
						throw new Exception("Unable to connect to database");
					}
				}
			}
		}
		$this->link = $link;

		// make sure at current schema version, if not, update
		$this->checkVersion();

		return true;
	}

	public function runFile($file) {
		if (!file_exists($file)) {
			throw new Exception("File $file does not exist.");
		}
		$contents = file_get_contents($file);
		if (!$contents) {
			throw new Exception("Unable to read the contents of $file.");
		}

		// split all the query's into an array
		$sql = explode(';', $contents);
		foreach ($sql as $query) {
			if (!empty ($query)) {
				$result = mysql_query($query);

				if (!$result) {
					throw new Exception(mysql_error());
				}
			}
		}
		return true;
	}

	// WARNING: experimental feature, may be moved out of framework due to number of site specific variables
	// NOTE: assumes schema versioning in framework specific format, will not function otherwise
	public function getVersion() {
		$result = mysql_query("SHOW TABLES LIKE 'ubarmetainfo'");
		if (!$result) {
			throw new Exception('Could not execute query:' . mysql_error());
		}

		// table does not exist, create it and indicate on version 0
		if (mysql_num_rows($result) == 0) {
			$this->runFile(UBAR_ROOT . "bootstrap.sql");
			return 0;
		}

		$result = mysql_query("SELECT val FROM ubarmetainfo WHERE name='schemaversion'");
		if (!$result) {
			throw new Exception('Could not execute query:' . mysql_error());
		}
		// if entry not found, assume a schema version of 0
		if (mysql_num_rows($result) == 0) {
			return 0;
		}
		return (int) mysql_result($result, 0, "val"); // outputs third employee's name
	}

	// only tries to update schema if current version and path to upgrade sql files are defined
	public function checkVersion() {
		if (defined("SCHEMA_PATH") && defined("SCHEMA_VERSION") && !is_null(SCHEMA_PATH) && !is_null(SCHEMA_VERSION)) {
			$version = $this->getVersion();
			if ($version < SCHEMA_VERSION) {
				$rootName = SCHEMA_PATH;
				for ($i = $version + 1; $i <= SCHEMA_VERSION; $i++) {
					$curPath = str_replace("*", $i, SCHEMA_PATH);
					$this->runFile(UBAR_ROOT . $curPath);
				}
				// update version in db
				$result = mysql_query("UPDATE ubarmetainfo SET val=" . SCHEMA_VERSION . " WHERE name='schemaversion'");
				if (!$result) {
					throw new Exception('Could not execute query:' . mysql_error());
				}
			}
		}
	}

	public function getLink() {
		return $this->link;
	}

	public function getLastError() {
		return mysql_errno($this->link) . ": " . mysql_error($this->link);
	}

	//properly escapes a string, taking into account the connection's character set and whether magic quotes is on
	public function escapeString($string) {
		// if magic quotes is on, strip slashes to avoid double escape
		if(get_magic_quotes_gpc()) {
			$string = stripslashes($string);
		}
		return mysql_real_escape_string($string, $this->link);
	}
}
?>
