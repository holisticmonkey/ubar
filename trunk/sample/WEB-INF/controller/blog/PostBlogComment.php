<?php
class PostBlogComment extends BaseAction {

	const CAPTCHA_PRIVATE_KEY = '6Lfj4gsAAAAAAKbsqPnOuyrSYjrfeV0oY7PMH980';

	const CAPTCHA_COOKIE_NAME = "captcha";

	private $name;

	private $contents;

	private $blogId;

	private $captchaChallenge;

	private $captchaResponse;

	public function setName($name) {
		$this->name = $name;
	}

	public function setContents($contents) {
		$this->contents = $contents;
	}

	public function setBlogId($blogId) {
		$this->blogId = $blogId;
	}

	// surfaced for param in result
	public function getBlogId() {
		return $this->blogId;
	}

	public function setRecaptcha_challenge_field($challenge) {
		$this->captchaChallenge = $challenge;
	}

	public function setRecaptcha_response_field($response) {
		$this->captchaResponse = $response;
	}

	public function executeInner() {
		// instantiate db
		$dbManager = new DBManager();

		// make comments safe and nicely formatted
		// TODO: strip tags with exceptions (see examples at http://us2.php.net/manual/en/function.strip-tags.php)
		// allowable tags <b><strong><u><i><a><em> possibly allowable <ul><ol><li>
		// TODO: convert "safe" tags to safe implementations, ex <strong style="foo"></strong> becomes <strong></strong>
		// TODO: sanitize anchor tags, ex <a href="javascript://"> is killed and <a href="foo"> becomes <a href="foo" target="_blank">

		// escape strings for insert
		$name = $dbManager->escapeString($this->name);
		$contents = $dbManager->escapeString($this->contents);

		// do query
		$result = mysql_query("INSERT INTO blogcomments SET blogid=" . $this->blogId . ",name='$name', message='$contents'");

		// check if successful
		if ($result) {
			$this->addNotice("Successfully posted a blog entry from \"" . $this->name . "\".");

			// TODO: determine why trend micro firewall causing this to hang and why email not sending even when not hanging
			//$this->notifyAdmins();
		} else {
			$this->addError("An error occured attempting to add a blog post. " . $dbManager->getLastError());
		}

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants :: SUCCESS;
	}

	public function validateUserInput() {
		if (Str :: nullOrEmpty($this->name)) {
			$this->addError("generic.error.missingRequiredField", array (
				'field' => 'name'
			), 'name');
		}
		if (Str :: nullOrEmpty($this->contents)) {
			$this->addError("generic.error.missingRequiredField", array (
				'field' => 'contents'
			), 'contents');
		}
		if (Str :: nullOrEmpty($this->blogId)) {
			$this->addError("generic.error.missingRequiredField", array (
				'field' => 'blogId'
			));
		}

		// check captcha
		if (!Str :: nullOrEmpty($this->captchaChallenge)) {
			$resp = ReCaptcha :: recaptcha_check_answer(self :: CAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $this->captchaChallenge, $this->captchaResponse);

			if ($resp->is_valid) {
				// DAY is in milliseconds, convert to seconds and multiply by 14 for 2 weeks
				$expireTime = time() + GlobalConstants :: DAY / 10 * 14;
				setcookie(self :: CAPTCHA_COOKIE_NAME, true, $expireTime, '/');
			} else {
				$this->addError($resp->error);
			}
		} elseif (!isset ($_COOKIE[self :: CAPTCHA_COOKIE_NAME])) {
			$this->addError("Captcha does not appear to be functioning properly, please contact site administrator.");
		}
	}

	private function notifyAdmins() {
		// get email addresses for each admin
		$result = mysql_query("SELECT email FROM users WHERE isadmin = true");

		$addresses = array ();

		if (!is_null($result) && $result && mysql_num_rows($result) > 0) {
			while ($user = mysql_fetch_object($result, 'User')) {
				array_push($addresses, $user->getEmail());
			}
		}

		// clean comment for sending
		$messageBody = strip_tags($this->contents);
		$messageBody = wordwrap($messageBody);
		if(preg_match("/(TO:)|(CC:)|(CCO:)|(Content-Type)/", $messageBody)) {
			$messageBody = "Comment contents suppressed due to suspected header injection";
		}

		$from = "notifications@holisticmonkey.com";
		$subject = "New Blog Comment Posted";
		$header = "From: $from\r\n";
		ini_set('sendmail_from', $from);

		// send to each admin
		foreach ($addresses as $address) {
			//die("trying to send mail");
			mail($address, $subject, $messageBody, $header);
		}

	}

}
?>