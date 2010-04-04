<?php
class LogOut extends BaseAction {
	private $referringPage;

	public function setReferringPage($page) {
		$this->referringPage = $page;
	}

	public function getReferringPage() {
		return $this->referringPage;
	}

	protected function executeInner() {
		unset($_SESSION[ValidateCredentials::USER_KEY]);

		$this->addNotice("logOut.notice.loggedOut");
	}
}
?>