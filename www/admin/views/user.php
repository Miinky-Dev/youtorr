<?php
/**
* This file is part of youtorr
*
* @author Jean-Lou Hau
* @copyright 2013 Jean-Lou Hau sybix@jeannedhack.org
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with youtorr.  If not, see <http://www.gnu.org/licenses/>.
*
*/
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
                                                                                                                                                                               

