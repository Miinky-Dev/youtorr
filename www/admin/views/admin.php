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
if(!defined('YOUTORR') || $_SESSION['role']!=100){
	die(1);
}
?>
<fieldset><legend>Users : </legend>
<?php
if(isset($userMsg) && count($userMsg)>0){
	echo "Problemes : <br />";
	foreach($userMsg as $error){
		echo $error."<br />";
	}
}
?>
<fieldset><legend>Add youtorr user :</legend>
<form method="post">
<input type="hidden" name="action" value="add" />
Name : <input type="text" name="login" /><br />
Email : <input type="text" name="email" /><br />
Password : <input type="password" name="password1" /><br />
Retype password : <input type="password" name="password2" /><br />
Role :
<select name="role">
	<option value="admin">admin</option>
	<option value="user" selected="selected">user</option>
</select><br />
<input type="submit" value="Add"/>
</form>
</fieldset>
<fieldset><legend>User list</legend>
<form method="post" />
<select name="action">
<option value="delete">Delete</option>
</select><br />
<?php
foreach($userList as $user){
	echo '<input type="checkbox" name="id[]" value="'.$user['id'].'"/>'.$user['login'].' : '.$user['role'].'<br />';
}
?>
<input type="submit"/>
<form>
</fieldset>
</fieldset>
<?php if(count($errors) > 0){ ?>
<fieldset><legend>Errors</legend>
<?php 
foreach($errors as $error){
	echo date("m/d/y H:i",$error['timestamp']).' : '.$error['type'].' '.$error['url'].' '.$error['message'].'<br />';
}
?>
</fieldset>
<?php } ?>
<fieldset><legend>Channel :</legend>
<?php
foreach($channels as $channel){
	echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$channel['channel']."'>".$channel['channel']."</a> : Video : ".$channel['nbVideo']." Followers : ".$channel['nbFollower']."<br />";
}
?>
</legend>

<fieldset><legend>Infos :</legend>
Videos <?php echo $nbVideos;?><br />
User channels <?php echo $nbChannels;?><br />
Space : <?php echo "$currentSize / ".$config['maxSize']; ?><br />
<?php echo "$remain videos remaining"; ?><br />
<fieldset><legend>Current downloading :</legend>
<?php echo $videoLog; ?>
</fieldset>
<fieldset><legend>Channel downloading :</legend>
<?php echo $channelLog; ?>
</fieldset>
</fieldset>
