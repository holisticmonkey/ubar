<?php
class PostBlogUpdate extends PostBlog {

	private $blogId;

	public function setBlogId($id) {
		$this->blogId = $id;
	}

	public function getBlogId() {
		return $this->blogId;
	}

	public function executeInner() {
		// instantiate db
		$dbManager = new DBManager();

		// escape strings for insert
		$title = $dbManager->escapeString($this->postTitle);
		$contents = $dbManager->escapeString($this->contents);

		// do query
		$result = mysql_query("UPDATE blog SET title='$title', message='$contents' WHERE blogid = " . $this->blogId);

		// check if successful
		if($result) {
			$this->addNotice("blog.notice.blogUpdated", array('id' => $this->blogId));
		} else {
			$this->addError("blog.error.failedBlogUpdate", array("error" => $dbManager->getLastError()));
		}

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants::SUCCESS;
	}

	public function validateUserInput() {
		parent::validateUserInput();
		if(Str::nullOrEmpty($this->blogId)) {
			$this->addError("generic.error.missingRequiredField", array("field" => "blogId"));
		}
	}
}
?>