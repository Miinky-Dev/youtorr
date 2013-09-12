<?php
if(!defined('YOUTORR')){
	die(1);
}
?>
<form method="post">
<fieldset><legend>Identity</legend>
<input type="hidden" name="updateProfile" value="update" />
<input type="text" name="login" value="<?php echo $_SESSION['login']; ?>" readonly/><br />
<input type="text" name="mail" value="<?php echo $_SESSION['mail']; ?>" /><br />
</fieldset>
<fieldset><legend>Change password</legend>
New <input type="password" name="newPassword"  /><br />
Retype new<input type="password" name="newPassword2"  /><br />
</fieldset>
<input type="hidden" name="updateProfile" value="update" />
Password <input type="password" name="Password"  /><br />
<input type="submit" name="submit" value="Update profile" />
</form>
                                                                                                                                                                               

