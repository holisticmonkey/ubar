<?php
abstract class BaseAction extends Action {

	public function __construct($def) {
		parent :: __construct($def);

		if(!function_exists("isAdmin")) {
			function isAdmin() {
				global $action;
				return $action->isAdmin();
			}

			function isLoggedIn() {
				global $action;
				return $action->isLoggedIn();
			}

			function getUser() {
				global $action;
				return $action->getUser();
			}
		}
	}

	public function isAdmin() {
		$user = $this->getUser();
		if(is_null($user)) {
			return false;
		}
		return $user->isAdmin();
	}

	public function isLoggedIn() {
		if(is_null($this->getUser())) {
			return false;
		}
		return true;
	}

	public function getUser() {
		if(isset($_SESSION[ValidateCredentials::USER_KEY])) {
			return $_SESSION[ValidateCredentials::USER_KEY];
		}
		return null;
	}
}
?>
