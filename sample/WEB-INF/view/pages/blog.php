<?php
$publickey = '6Lfj4gsAAAAAADTlDGnQtTpREZ-v4g5jggodUC7k';
// get page info
$post = get("post");
$comments = get("comments");
?>
<!-- IF IS_ADMIN > display post blog toggle link -->
<?php if(isAdmin()) { ?>
	<style>
		#postForm { display: none; }
		#editPostForm { display: none; }
	</style>
	<script type="text/javascript">
	function togglePost() {
		var postForm = returnObject('postForm');
		var postToggle = returnObject('postToggle');
		if(postForm.style.display == 'none' || postForm.style.display == '') {
			postForm.style.display = 'block';
			postToggle.style.display = 'none';
		} else {
			postForm.style.display = 'none';
			postToggle.style.display = 'block';
		}
	}
	function toggleEditPost() {
		var form = returnObject('editPostForm');
		var original = returnObject('originalPost');
		if(form.style.display == 'none' || form.style.display == '') {
			form.style.display = 'block';
			original.style.display = 'none';
		} else {
			form.style.display = 'none';
			original.style.display = 'block';
		}
	}
	</script>
	<a id="postToggle" href="javascript://" onclick="togglePost()">Post New</a>
	<div id="postForm" >
		<form action="PostBlog.action" method="post">
			<div class="label">Title</div>
			<div class="contents"><input type="text" size="100" name="postTitle" value="<?php echo getUserInput("postTitle") ?>"/></div>

			<div class="label">Contents</div>
			<div class="contents"><textarea rows="30" cols="100" name="contents"><?php echo getUserInput("contents") ?></textarea></div>

			<div class="contents"><input type="submit" name="submit" value="Update" /> <input type="button" name="button" value="Cancel" onclick="togglePost()" /></div>
		</form>
	</div>
	<?php if(!is_null($post)) { ?>
		<div id="editPostForm">
			<form action="PostBlogUpdate.action" method="post">
				<div class="label">Title</div>
				<div class="contents"><input type="text" size="100" name="postTitle" value="<?php echo $post->getTitle() ?>"/></div>

				<div class="label">Contents</div>
				<div class="contents"><textarea rows="30" cols="100" name="contents"><?php echo $post->getMessage() ?></textarea></div>

				<div class="contents"><input type="submit" name="submit" value="Post" /> <input type="button" name="button" value="Cancel" onclick="toggleEditPost()" /></div>

				<input type="hidden" name="blogId" value="<?php echo $post->getId() ?>" />
			</form>
		</div>
	<?php } ?>
<?php } ?>

<?php if(!is_null($post)) { ?>
<div id="originalPost">
	<div class="label"><?php echo $post->getTitle() ?> <span class="aside"><?php echo $post->getDateCreated() ?></span></div>
	<div class="contents" style="padding-top: 5px;"><?php echo $post->getMessage() ?></div>
	<?php if(isAdmin()) { ?>
		<div class="contents" style="text-align: right"><a href="DeleteBlogPost.action?blogId=<?php echo $post->getId() ?>">delete</a> | <a href="javascript://" onclick="toggleEditPost()">edit</a></div>
	<?php } ?>
</div>

<div style="font-weight: bold;  margin: 40px 0px 10px 0px;"><?php echo getTxt("blog.text.numComments", array(count($comments)))?></div>

<div style="padding-left: 30px;">
	<?php foreach($comments as $comment) { ?>
		<div class="label"><?php echo $comment->getName() ?> <span class="aside"><?php echo $comment->getDateCreated() ?><?php if(isAdmin()) { ?> <a href="DeleteBlogComment.action?commentId=<?php echo $comment->getId() ?>&amp;blogId=<?php echo get("blogId") ?>">delete</a><?php } ?></span></div>
		<div class="contents"><?php echo $comment->getMessage() ?></div>
	<?php } ?>



	<div id="comments">
		<form action="PostBlogComment.action" method="post">
			<?php
			if(!isset($_COOKIE[PostBlogComment::CAPTCHA_COOKIE_NAME])) {
				echo ReCaptcha :: recaptcha_get_html($publickey);
			}
			?>
			<?php if(hasErrorsOrWarningsForField('name')) {
				$messages = getErrorsOrWarningsForField('name');
				?>
				<div class="inlineError">
					<ul>
						<?php foreach($messages as $message) { ?>
						<li><?php echo $message->getMessage() ?></li>
						<?php } ?>
					</ul>
				</div>
			<?php } ?>
			<div class="contents">Name: <input type="text" size="40" name="name" value="<?php echo getUserInput("name") ?>" /></div>

			<div class="contents"><textarea rows="5" cols="50" name="contents"><?php echo getUserInput("contents") ?></textarea></div>

			<div class="contents"><input type="submit" name="submit" value="Comment" /></div>
			<input type="hidden" name="blogId" value="<?php echo $post->getId() ?>" />
		</form>
	</div>
</div>
<?php } ?>