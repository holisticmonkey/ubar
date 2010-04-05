<form action="ValidateCredentials.action" method="post">
	<div class="label">Email Address</div>
	<div class="contents"><input type="text" name="email" value="<?php echo getUserInput("email") ?>" /></div>

	<div class="label">Password</div>
	<div class="contents"><input type="password" name="password" value="<?php echo getUserInput("password") ?>" /></div>

	<div class="contents"><input type="submit" name="submit" value="Log In" /></div>

	<input type="hidden" name="referringPage" value="<?php echo get("referringPage") ?>" />
</form>