<?php
class User {

	private $userid;

	private $email;

	private $username;

	private $password;

	private $isadmin = false;

	public function __construct() {
		// noop, instantiated from mysql_fetch_object()
	}

	public function getEmail() {
		return $this->email;
	}

	public function getUsername() {
		return $this->username;
	}

	// note, this is stored in the db encrypted
	public function getPassword() {
		return $this->password;
	}

	public function isAdmin() {
		return $this->isadmin;
	}

	public function validatePassword($testPass) {
		return md5($testPass) == $this->password;
	}

	public function getUserid() {
		return $this->userid;
	}
}
?>
