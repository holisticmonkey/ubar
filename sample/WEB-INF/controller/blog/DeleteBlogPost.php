<?php
class DeleteBlogPost extends BaseAction {

	private $blogId;

	public function setBlogId($id) {
		$this->blogId = $id;
	}

	public function getBlogId() {
		return $this->blogId;
	}

	protected function executeInner() {
		// instantiate db
		$dbManager = new DBManager();

		// do query
		$result = mysql_query("DELETE FROM blog WHERE blogid = " . $this->blogId);

		// check if successful
		if ($result) {
			$this->addNotice("blog.notice.postDeleted", array("id" => $this->blogId));

			// delete associated comments
			$result = mysql_query("DELETE FROM blogcomments WHERE blogid = " . $this->blogId);
		} else {
			$this->addError("blog.error.failedPostDeletion", array("id" => $this->blogId, "error" => $dbManager->getLastError()));
		}

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants :: SUCCESS;
	}

	public function validateUserInput() {
		if(Str::nullOrEmpty($this->blogId)) {
			$this->addError("generic.error.missingRequiredField",  array (
				'field' => 'blogId'
			));
		}
		if(!isAdmin()) {
			$this->addError("generic.error.insufficientPermissions");
		}
	}
}
?>