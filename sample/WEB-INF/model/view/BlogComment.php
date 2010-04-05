<?php
class BlogComment {

	private $commentid;

	private $name;

	private $message;

	private $created;

	public function __construct() {
		$this->created = date("H:i n.j.Y", strtotime($this->created));
	}

	public function getId() {
		return $this->commentid;
	}

	public function getName() {
		return $this->name;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getDateCreated() {
		return $this->created;
	}
}
?>