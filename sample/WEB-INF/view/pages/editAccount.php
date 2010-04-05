<form action="UpdateAccount.action" method="post">
	<div class="label">Email Address</div>
	<div class="contents"><input type="text" name="email" value="<?php echo get("email") ?>" /></div>

	<div class="label">Password</div>
	<div class="contents"><input type="password" name="password" value="<?php echo get("password") ?>" /></div>

	<div class="label">Password Confirm</div>
	<div class="contents"><input type="password" name="passwordConfirm" value="<?php echo get("passwordConfirm") ?>" /></div>

	<div class="contents"><input type="submit" name="submit" value="Update" /></div>
</form>