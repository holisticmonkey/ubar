<?php
class ValidateCredentials extends BaseAction {

	const USER_KEY = 'user';

	private $email;

	private $password;

	private $referringPage;

	public function setReferringPage($page) {
		$this->referringPage = $page;
	}

	public function getReferringPage() {
		return $this->referringPage;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	protected function executeInner() {
		$dbManager = new  DBManager();

		// prepare input for query
		$email = $dbManager->escapeString($this->email);

		// get user info from db
		$result = mysql_query("SELECT * FROM users WHERE email ='" . $email . "'");

		// if does not exist, add error and return user error
		if(mysql_num_rows($result) == 0) {
			$this->addError("login.error.invalidEmail", array("email" => $this->email), "email");
			return GlobalConstants::USER_ERROR;
		}

		// convert result into user object
		$user = mysql_fetch_object($result, 'User');

		// check md5 of submitted password and what is stored in the db
		if(!$user->validatePassword($this->password)) {
			$this->addError("login.error.invalidPassword", null, "email");
			return GlobalConstants::USER_ERROR;
		}

		$this->addNotice("login.notice.credentialsValid", array("email" => $this->email));

		// push user object into session
		$_SESSION[self::USER_KEY] = $user;
	}

	protected function validateUserInput() {
		if (Str :: nullOrEmpty($this->email)) {
			$this->addError("generic.error.missingRequiredField", array('field' => 'email'), 'email');
		}
		if (Str :: nullOrEmpty($this->password)) {
			$this->addError("generic.error.missingRequiredField", array('field' => 'password'), 'password');
		}
	}
}
?>
