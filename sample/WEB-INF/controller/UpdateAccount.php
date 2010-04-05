<?php
class UpdateAccount extends BaseAction {

	private $email;

	private $password;

	private $passwordConfirm;

	public function setEmail($email) {
		$this->email = $email;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function setPasswordConfirm($passwordConfirm) {
		$this->passwordConfirm = $passwordConfirm;
	}

	protected function executeInner() {
		// update user in database
		$userid = $this->getUser()->getUserid();

		// instantiate db
		$dbManager = new DBManager();

		// escape strings for insert
		$email = $dbManager->escapeString($this->email);

		$result = null;

		if(!Str :: nullOrEmpty($this->password)) {
			// they put something in for password, update it
			$password = md5($this->password);
			$result = mysql_query("UPDATE users SET email='$email', password='$password' WHERE userid = $userid");
		} else {
			// just update email
			$result = mysql_query("UPDATE users SET email='$email' WHERE userid = $userid");
		}

		// check if successful
		if (!$result) {
			$this->addError("An error occured attempting update user info. " . $dbManager->getLastError());
			return GlobalConstants::USER_INPUT;
		}

		$this->addNotice("Successfully updated user info for \"" . $this->email . "\".");

		// get new user object
		$result = mysql_query("SELECT * FROM users WHERE userid = $userid");

		$user = mysql_fetch_object($result, 'User');

		// update user object in session
		$_SESSION[ValidateCredentials::USER_KEY] = $user;

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants :: SUCCESS;
	}

	public function validateUserInput() {
		if(!$this->isLoggedIn()) {
			$this->addError("generic.error.notLoggedIn");
		}
		if (Str :: nullOrEmpty($this->email)) {
			$this->addError("generic.error.missingRequiredField", array (
				'field' => 'email'
			), 'email');
		}
		if ($this->password != $this->passwordConfirm) {
			$this->addError("editAccount.error.passwordMismatch");
		}
	}
}