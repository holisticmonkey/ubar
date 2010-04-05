<?php

// TODO: integrate with session check to allow editing
class Blog extends BaseAction {

	private $blogId;

	private $post;

	private $comments = array ();

	private $posts = array ();

	public function getPost() {
		return $this->post;
	}

	public function getComments() {
		return $this->comments;
	}

	public function getPosts() {
		return $this->posts;
	}

	public function setBlogId($id) {
		$this->blogId = $id;
	}

	public function getBlogId() {
		return $this->blogId;
	}

	public function executeInner() {
		// asemble blog articles from database
		$dbManager = new DBManager();

		$result = null;

		// if specific id is set, get that blog and associated comments
		if (!is_null($this->blogId)) {
			$result = mysql_query("SELECT * FROM blog WHERE blogid = " . $this->blogId);
			if (!$result || mysql_num_rows($result) == 0) {
				$this->addError("blog.error.badId", array("id" =>$this->blogId));
			}
		}
		// if no id or bad id, get most recent post
		if (is_null($result) || !$result || mysql_num_rows($result) == 0) {
			$result = mysql_query("SELECT * FROM blog WHERE blogid = (SELECT MAX(blogid) FROM blog)");
			if (!$result || mysql_num_rows($result) == 0) {
				$this->addError("blog.error.noPosts");
			}
		}

		if ($result && mysql_num_rows($result) == 1) {
			// get post info
			$this->post = mysql_fetch_object($result, 'BlogPost');

			// add id to local vars
			$this->blogId = $this->post->getId();

			// free results
			mysql_free_result($result);

			// get comments associated with post and make available as list
			$result = mysql_query("SELECT * FROM blogcomments WHERE blogid = " . $this->post->getId());
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_object($result, 'BlogComment')) {
					array_push($this->comments, $row);
				}
			}

			// free results
			mysql_free_result($result);

			// get all blog entries for listing
			$result = mysql_query("SELECT blogid, title, created FROM blog ORDER BY blogid DESC");
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_object($result, 'BlogPost')) {
					array_push($this->posts, $row);
				}
			}
		}

		// else get most recent
		return GlobalConstants :: SUCCESS;
	}
}
?>