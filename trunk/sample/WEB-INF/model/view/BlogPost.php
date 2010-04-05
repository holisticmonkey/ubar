<?php
class BlogPost {

	private $blogid;

	private $title;

	private $message;

	private $created;

	public function __construct() {
		$this->created = date("n.j.Y", strtotime($this->created));
	}

	public function getId() {
		return $this->blogid;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getDateCreated() {
		return $this->created;
	}
}
?>