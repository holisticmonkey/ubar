<?php
class ViewAction extends Action {

	private $exception;

	private $title;
	private $page;
	private $section;

	protected function setException($exception) {
		$this->exception = $exception;
	}

	public function getTitle() {
		return $this->title;
	}

	protected function setPage($page) {
		$this->page = $page;
	}

	public function getPage() {
		return $this->page;
	}

	public function getSection() {
		return $this->section;
	}
}