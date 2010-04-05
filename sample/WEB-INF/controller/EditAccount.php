<?php
class EditAccount extends BaseAction {

	private $email;

	private $password;

	private $passwordConfirm;

	public function getEmail() {
		return $this->email;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getPasswordConfirm() {
		return $this->passwordConfirm;
	}

	protected function executeInner() {
		// check that logged in before manipulating user
		if(!$this->isLoggedIn()) {
			$this->addError("generic.error.notLoggedIn");
			return GlobalConstants::ERROR;
		}

		// populate form fields with error values or defaults
		$user = $this->getUser();
		$email = $this->getUserInput("email");
		$password = $this->getUserInput("password");
		$passwordConfirm = $this->getUserInput("passwordConfirm");

		if(is_null($email)) {
			$this->email = $user->getEmail();
		} else {
			$this->email = $email;
		}

		if(!is_null($password)) {
			$this->password = $password;
		}

		if(!is_null($passwordConfirm)) {
			$this->passwordConfirm = $passwordConfirm;
		}

		return GlobalConstants::SUCCESS;
	}

	public function validateUserInput() {

	}
}
?>