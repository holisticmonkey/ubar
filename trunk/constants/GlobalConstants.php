<?
class GlobalConstants {

	# Time constants in milliseconds
	// TODO: determine if some way to specify something similar to "const MINUTE = 60 * SECOND"
	const SECOND = 1000;
	const MINUTE = 60000;
	const HOUR = 3600000;
	const DAY = 86400000;

	# INI standard values
	const INI_ON = 'On';
	const INI_OFF = 'Off';

	# Default supported return strings for control conditions of actions
	const SUCCESS = 'SUCCESS';
	const ERROR = 'ERROR';
	const USER_ERROR = 'USER_ERROR';
	const JSON = 'JSON';
	const ILLEGAL_ACCESS = 'ILLEGAL_ACCESS';
	const AUTH = 'AUTH';
	const JSON_AUTH = 'JSON_AUTH';
	const SETUP = 'SETUP';

	# Ubar default config values in case not found in ubar_config.properties
	// default locale string
	const LOCALE_DEFAULT = 'english-usa,en_US.utf8';
	// display errors
	// NOTE: dev mode will override this
	const DISPLAY_ERRORS = TRUE;

	// display markup in errors
	const HTML_ERRORS = FALSE;

	// error reporting level
	const ERROR_LEVEL = 'E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR';
	// dev mode override
	const ERROR_LEVEL_DEV_MODE = 'E_ALL|E_STRICT';

	// error logging
	const LOG_ERRORS = FALSE;
	const LOG_ERRORS_DEV_MODE = TRUE;

	// use magic quotes
	// NOTE: if this is set to off, you will need to escape quotes or use Str::condAddSlashes() Str::condStripSlashes()
	const MAGIC_QUOTES = FALSE;

	// database connection info
	// connect to db on any page that is init'd
	// NOTE: if this is turned off, you should have a page specific way of turning on the connection
	const DB_ALWAYS_CONNECT = TRUE;
	const DB_USE = FALSE;

	// session lifetime
	const SESSION_LIFETIME = 86400000;

	// TODO: add file upload configuration

	# DEFAULT CHARSET
	const CHARSET = 'UTF-8';

	# USE XHTML? - only adds header if browser supports it
	const USE_XHTML = FALSE;

	// dev mode - make error reporting more verbose, use dev mode specific config from above, possible other dev specific settings
	const DEV_MODE = FALSE;

	# DEFAULT ACTION - default action to load if not found in ubar_config
	const DEFAULT_ACTION = 'default';

	# DUMMY_ACTION - class name for dummy action when no class specified
	const DUMMY_ACTION = 'DummyAction';

	# ROOT NAME OF PROPS FILE - root name of localized properties files. for example "resources_de.properties" would have a root of "resources"
	const PROPERTIES_ROOT = 'resources';

	const BASE_ACTION_PATH			= "../WEB-INF/controller/";
	const BASE_VIEW_PATH			= "../WEB-INF/view/";
	const BASE_PROPERTIES_PATH		= "../WEB-INF/properties/";


	# Exception codes
}
?>