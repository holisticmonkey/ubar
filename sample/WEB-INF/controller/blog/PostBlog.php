<?php
class PostBlog extends BaseAction {

	protected $postTitle;

	protected $contents;

	public function setPostTitle($title) {
		$this->postTitle = $title;
	}

	public function setContents($contents) {
		$this->contents = $contents;
	}

	public function executeInner() {
		// instantiate db
		$dbManager = new DBManager();

		// escape strings for insert
		$title = $dbManager->escapeString($this->postTitle);
		$contents = $dbManager->escapeString($this->contents);

		// do query
		$result = mysql_query("INSERT INTO blog SET title='$title', message='$contents'");

		// check if successful
		if($result) {
			$this->addNotice("blog.notice.blogPosted");
		} else {
			$this->addError("blog.error.failedBlogPost", array("error" => $dbManager->getLastError()));
		}

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants::SUCCESS;
	}

	public function validateUserInput() {
		if(Str::nullOrEmpty($this->postTitle)) {
			$this->addError("generic.error.missingRequiredField", array("field" => "postTitle"));
		}
		if(Str::nullOrEmpty($this->contents)) {
			$this->addError("generic.error.missingRequiredField", array("field" => "contents"));
		}
	}
}
?>