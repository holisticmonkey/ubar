<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php if(getTitle() != '') { echo( getTitle() . ": "); } ?>Site Name</title>
	<link type="text/css" rel="stylesheet" href="/css/default.css" media="Screen" />
	<script type="text/javascript" src="/js/default.js"></script>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="791">
	<tr>
		<td id="header"><img src="/images/logo.jpg" alt="Site Name" width="143" height="35" /></td>
	</tr>
	<tr>
		<td id="navBar">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<a href="Home.action"<?php if(getActionName() == "Home") { ?> class="on"<?php } ?>>Home</a>
						.
						<a href="FineArt.action"<?php if(getSection() == "Portfolio") { ?> class="on"<?php } ?>>Portfolio</a>
						.
						<a href="Blog.action"<?php if(getActionName() == "Blog") { ?> class="on"<?php } ?>>Blog</a>
						.
						<a href="Contact.action"<?php if(getActionName() == "Contact") { ?> class="on"<?php } ?>>Contact</a>
					</td>
					<td align="right">
						<?php if(isLoggedIn()){ ?>
							<a href="EditAccount.action">Edit Account</a> . <a href="LogOut.action?referringPage=<?php echo $_SERVER['REQUEST_URI'] ?>">Logout</a>
						<?php } else { ?>
							<a href="Login.action?referringPage=<?php echo $_SERVER['REQUEST_URI'] ?>" <?php if(getActionName() == "Login") { ?> class="on"<?php } ?>>Login</a>
						<?php }?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" width="100%" height="300">
				<tr>
					<td id="sidebar">
						<!-- update test to be more performant and include all sections with sub nav -->

						<table cellpadding="0" cellspacing="0">
							<?php if(getSection() == "portfolio"){?>
								<tr>
									<td class="navSubSection"><a href="FineArt.action" class="subNav<?php if(getSubSection()=="fineArt"){?>On<?php } ?>">Fine Art</a></td>
								</tr>
								<tr>
									<td class="navSubSection"><a href="Programming.action" class="subNav<?php if(getSubSection()=="programming"){?>On<?php } ?>">Programming</a></td>
								</tr>
								<tr>
									<td class="navSubSection"><a href="ProductDesign.action" class="subNav<?php if(getSubSection()=="productDesign"){?>On<?php } ?>">Product Design</a></td>
								</tr>
							<?php } elseif(getSection() == "blog"){
								$posts = get("posts");
							?>
								<tr>
									<td id="blogPosts">
									<?php foreach($posts as $curPost) { ?>
										<div><a href="Blog.action?blogId=<?php echo $curPost->getId() ?>"><?php echo $curPost->getTitle() ?></a> <span class="aside"><br /><?php echo $curPost->getDateCreated() ?></span></div>
									<?php }?>
									</td>
								</tr>
							<?php } else { ?>
								<tr>
									<td>&nbsp;</td>
								</tr>
							<?php } ?>
						</table>

						<br />&nbsp;
					</td>
					<td id="body">
						<?php
						if(hasErrors()){ ?>
							<div id="errorBox">
								The following errors occured:
								<ul>
									<?php
									$errors = getErrors();
									$warnings = getWarnings();
									$allErrors = array_merge($errors, $warnings);
									foreach($allErrors as $error){ ?>
										<li><?php echo $error->getMessage() ?></li>
									<?php } ?>
								</ul>
							</div>
						<?php
						}
						if(hasNotices()){?>
							<div id="noticeBox">
								<?php
								$notices = getNotices();
								foreach($notices as $notice){?>
									<p><?php echo $notice->getMessage() ?></p>
								<?php } ?>
							</div>

						<?php } ?>

						<?php renderBody(); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td id="footer">
			<table cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td id="footerLinks"><a href="Privacy.action">privacy</a> . <a href="Contact.action">contact</a></td>
					<td id="copy">&copy; <?php echo date("Y", time()) ?> Copyright Message</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>