<?
/**
 * Class definition for GlobalConstants
 * @package		core
 */

/**
 * Generic framework constants
 *
 * A catch-all for constants used in this framework.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	constants
 *
 * @todo Split off into separate constants files.
 * @todo Determine if some way to specify something similar to "const MINUTE = 60 * SECOND"
 */
class GlobalConstants {

	# Time constants in milliseconds
	/**
	 * @var integer Milliseconds in a second.
	 */
	const SECOND = 1000;
	/**
	 * @var integer Milliseconds in a minute.
	 */
	const MINUTE = 60000;
	/**
	 * @var integer Milliseconds in a hour.
	 */
	const HOUR = 3600000;
	/**
	 * @var integer Milliseconds in a day.
	 */
	const DAY = 86400000;

	# INI standard values
	/**
	 * @var string INI value for turning a state on.
	 */
	const INI_ON = 'On';
	/**
	 * @var string INI value for turning a state off.
	 */
	const INI_OFF = 'Off';

	# Default supported return strings for control conditions of actions
	/**
	 * @var string Result string for success.
	 */
	const SUCCESS = 'SUCCESS';
	/**
	 * @var string Result string for error.
	 */
	const ERROR = 'ERROR';
	/**
	 * @var string Result string for invalid user input.
	 */
	const USER_ERROR = 'USER_ERROR';
	/**
	 * @var string Result string for rendering the contents in json.
	 */
	const JSON = 'JSON';
	/**
	 * @var string Result string for invalid permissions.
	 */
	const ILLEGAL_ACCESS = 'ILLEGAL_ACCESS';
	/**
	 * @var string Result string for requiring authentication.
	 */
	const AUTH = 'AUTH';
	/**
	 * @var string Result string for requiring authentication with a json request.
	 */
	const JSON_AUTH = 'JSON_AUTH';
	/**
	 * @var string Result string for web application not being set up.
	 */
	const SETUP = 'SETUP';
	/**
	 * @var string Default result string.
	 */
	const DEFAULT_NAME = self::SUCCESS;

	# Possible result types
	/**
	 * @var string Forward to a new action.
	 */
	const ACTION_TYPE = 'action';
	/**
	 * @var string Render a view.
	 */
	const PAGE_TYPE = 'page';
	/**
	 * @var string Render a file.
	 */
	const FILE_TYPE = 'file';
	/**
	 * @var string Forward to a url.
	 */
	const URL_TYPE = 'url';
	/**
	 * @var string Render as json.
	 */
	const JSON_TYPE = 'json';
	/**
	 * @var string Default result type.
	 */
	const DEFAULT_TYPE = self::FILE_TYPE;

	# Ubar default config values in case not found in ubar_config.properties
	/**
	 * @var string Default locale string.
	 */
	const LOCALE_DEFAULT = 'english-usa,en_US.utf8';
	/**
	 * @var boolean Display errors?.
	 * NOTE: DEV_MODE overrides this.
	 */
	const DISPLAY_ERRORS = TRUE;
	/**
	 * @var boolean Display errors with HTML formatting.
	 */
	const HTML_ERRORS = FALSE;

	/**
	 * @var string Error reporting levels.
	 */
	const ERROR_LEVEL = 'E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR';
	/**
	 * @var boolean Error reporting levels in DEV_MODE.
	 */
	const ERROR_LEVEL_DEV_MODE = 'E_ALL|E_STRICT';

	# error logging
	/**
	 * @var boolean Log errors.
	 * NOTE: not currently used.
	 */
	const LOG_ERRORS = FALSE;
	/**
	 * @var boolean Log errors in DEV_MODE.
	 * NOTE: not currently used.
	 */
	const LOG_ERRORS_DEV_MODE = TRUE;

	/**
	 * @var boolean Use magic quotes.
	 * NOTE: if this is set to off, you will need to escape quotes manually where necessary with addslashes()
	 */
	const MAGIC_QUOTES = FALSE;

	# database connection info
	/**
	 * @var boolean Use database configuration.
	 */
	const DB_USE = FALSE;

	/**
	 * @var integer Session lifetime in seconds. Value is equivelent to 1 day.
	 */
	const SESSION_LIFETIME = 86400000;

	/**
	 * @var string Default charset.
	 */
	const CHARSET = 'UTF-8';

	# USE XHTML? - only adds header if browser supports it
	/**
	 * @var boolean Configure responses for XHTML.
	 * @todo Make use of XHTML headers.
	 */
	const USE_XHTML = FALSE;

	// dev mode - make error reporting more verbose, use dev mode specific config from above, possible other dev specific settings
	/**
	 * @var boolean Default dev mode setting. This makes reporting more verbose
	 * and escalates some issues to fatal errors. It also influences which
	 * properties are used.
	 */
	const DEV_MODE = FALSE;

	/**
	 * @var string Class name for dummy action when no action defined.
	 */
	const DUMMY_ACTION = 'DummyAction';

	/**
	 * @var string Properties name root. For example "resources_de.properties"
	 * would have a root of "resources".
	 */
	const PROPERTIES_ROOT = 'resources';

	# default relative base paths
	/**
	 * @var string Default base path to model folder.
	 */
	const BASE_MODEL_PATH			= "../../model/";
	/**
	 * @var string Default base path to action folder.
	 */
	const BASE_ACTION_PATH			= "../../controller/";
	/**
	 * @var string Default base path to view folder.
	 */
	const BASE_VIEW_PATH			= "../../view/";
	/**
	 * @var string Default base path to properties folder.
	 */
	const BASE_PROPERTIES_PATH		= "../../properties/";
}
?>