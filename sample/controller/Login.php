<?php
class Login extends BaseAction {

	private $referringPage;

	public function setReferringPage($page) {
		$this->referringPage = $page;
	}

	public function getReferringPage() {
		return $this->referringPage;
	}

	public function executeInner() {
		return GlobalConstants::SUCCESS;
	}
}
?>