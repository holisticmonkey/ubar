<?php
class DeleteBlogComment extends BaseAction {

	private $commentId;

	private $blogId;

	public function setCommentId($id) {
		$this->commentId = $id;
	}

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
		$result = mysql_query("DELETE FROM blogcomments WHERE commentid = " . $this->commentId);

		// check if successful
		if ($result) {
			$this->addNotice("blog.notice.commentDeleted", array("id" => $this->commentId));
		} else {
			$this->addError("blog.error.failedCommentDeletion", array("id" => $this->commentId, "error" => $dbManager->getLastError()));
		}

		// return success regardless since returned to the same place and error displayed
		return GlobalConstants :: SUCCESS;
	}

	public function validateUserInput() {
		if(Str::nullOrEmpty($this->commentId)) {
			$this->addError("generic.error.missingRequiredField",  array (
				'field' => 'commentId'
			));
		}
		if(!isAdmin()) {
			$this->addError("generic.error.insufficientPermissions");
		}
	}
}
?>