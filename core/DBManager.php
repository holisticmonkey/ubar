<?php
/**
 * Class definition for DBManager
 * @package		core
 */

/**
 * Manager for database connectivity and schema versioning.
 *
 * This class manages connectivity to a mysql database, provides helper
 * methods, and supports db bootstrapping and automatic schema versioining.
 *
 * NOTE: This manager is rudimentary compared to a variety that exist elsewhere
 * and it is limited to mysql. If you are doing extensive db work, you may want
 * to disable the use of this in ubar_config.properties.
 *
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	utils
 *
 * @todo Add database specific exceptions.
 */
class DBManager {

	/**
	 * @var class Link to the mysql database.
	 */
	private $link;

	/**
	 * Initialize database connection with arguments defined in
	 * ubar_config.properties
	 *
	 * @throws An exception when it was unable to connect to the database.
	 * @throws An exception when required fields were not defined.
	 */
	public function __construct() {
		global $UBAR_GLOB;

		if (isset($UBAR_GLOB['DB_SERVER']) && isset($UBAR_GLOB['DB_USERNAME']) && isset($UBAR_GLOB['DB_PASSWORD']) && isset($UBAR_GLOB['DB_NAME'])) {
			$result = $this->connect($UBAR_GLOB['DB_SERVER'], $UBAR_GLOB['DB_USERNAME'], $UBAR_GLOB['DB_PASSWORD'], $UBAR_GLOB['DB_NAME'], true);
			if (!$result) {
				throw new Exception("Unable to connect to the database with the given credentials");
			}
		} else {
			throw new Exception("One or more required fields for connection to a database was not defined.");
		}
	}

	/**
	 * Connect to a mysql database with the given values.
	 *
	 * @param string $server Name of the server to connect to.
	 * @param string $user Username to conenct with.
	 * @param string $pass Password to connect with.
	 * @param string $dbName Name of the database to connect to.
	 * @param boolean $createIfNotExists Create the database if it doesn't
	 * already exist? Defaults to false.
	 *
	 * @return boolean True if successful.
	 *
	 * @throws An exception if it could not connect to the server or db.
	 */
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

	/**
	 * Execute a file as sql statements.
	 *
	 * @param string $file Path to file to execute.
	 *
	 * @return boolean Return true if successful.
	 *
	 * @throws An exception if the file does not exist.
	 * @throws An exception if unable to get the contents of the file.
	 * @throws An exception if unable to execute one or more of the sql
	 * statements.
	 */
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

	/**
	 * Get schema version. This requires ubar specific table to be created and versioning is
	 * handled through the naming convention of .sql files and a version entry
	 * in ubar_config.properties.
	 *
	 * If the table is not present, this method will attempt to bootstrap the
	 * table using bootstrap.sql.
	 *
	 * WARNING: This is an experimental feature and it should be used with care. Backup
	 * your database periodically if you are concerned about your data.
	 *
	 * @return intenger The current version of your schema. Defaults to 0 if no
	 * entry found in the database.
	 */
	public function getVersion() {
		global $UBAR_GLOB;

		$result = mysql_query("SHOW TABLES LIKE 'ubarmetainfo'");
		if (!$result) {
			throw new Exception('Could not execute query:' . mysql_error());
		}

		// table does not exist, create it and indicate on version 0
		if (mysql_num_rows($result) == 0) {
			$this->runFile($UBAR_GLOB['UBAR_ROOT'] . "bootstrap.sql");
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
	/**
	 * Check and update the schema version if necessary.
	 *
	 * While your database version is below the currently specified version in
	 * ubar_config.properties, it will use your SCHEMA_PATH naming convention
	 * to run schema files sequentially.
	 *
	 * @see DBManager::getVersion()
	 *
	 * @throws An exception if unable to update the schema version in the
	 * metainfo table.
	 */
	public function checkVersion() {
		global $UBAR_GLOB;

		if (isset($UBAR_GLOB['SCHEMA_PATH']) && isset($UBAR_GLOB['SCHEMA_VERSION']) && !is_null($UBAR_GLOB['SCHEMA_PATH']) && !is_null($UBAR_GLOB['SCHEMA_VERSION'])) {
			$version = $this->getVersion();
			if ($version < $UBAR_GLOB['SCHEMA_VERSION']) {
				$rootName = $UBAR_GLOB['SCHEMA_PATH'];
				for ($i = $version + 1; $i <= $UBAR_GLOB['SCHEMA_VERSION']; $i++) {
					$curPath = str_replace("*", $i, $UBAR_GLOB['SCHEMA_PATH']);
					$this->runFile($UBAR_GLOB['UBAR_ROOT'] . $curPath);
				}
				// update version in db
				$result = mysql_query("UPDATE ubarmetainfo SET val=" . $UBAR_GLOB['SCHEMA_VERSION'] . " WHERE name='schemaversion'");
				if (!$result) {
					throw new Exception('Could not execute query:' . mysql_error());
				}
			}
		}
	}

	/**
	 * Get the database link.
	 *
	 * @return class The database link.
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Get the last mysql error number and string.
	 *
	 * @return string The error message.
	 */
	public function getLastError() {
		return mysql_errno($this->link) . ": " . mysql_error($this->link);
	}

	//properly escapes a string, taking into account the connection's character set and whether magic quotes is on
	/**
	 * Escape a string for entry into the databse. This takes into account
	 * whether magic quotes is on.
	 *
	 * @param string $string The string for use in a query.
	 *
	 * @return string The escaped string.	 *
	 */
	public function escapeString($string) {
		// if magic quotes is on, strip slashes to avoid double escape
		if(get_magic_quotes_gpc()) {
			$string = stripslashes($string);
		}
		return mysql_real_escape_string($string, $this->link);
	}
}
?>