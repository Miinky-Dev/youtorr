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
if(isset($getChannel) && $getChannel!=''){
	echo "<fieldset><legend>$getChannel</legend>\n";
	echo "Videos downloaded ".$channel['videos']."<br />\n";
	echo "Videos reaming ".$channel['videos_tmp']."<br />\n";
	if(isset($channel['size'])){
		echo $channel['size']."/".$config['maxSize']."<br />\n";
	}
	echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=force'>Force check channel</a>\n";
	if($channel['delete']==0){
		echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=unfollow'>Unfollow channel</a><br />\n";
	}
	if($_SESSION['role'] == 100){
		if($channel['delete']==1){
			echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=undodelete'>Undo delete</a><br />\n";
		}else{
			echo "<a href='".$proto.$config['serverURL']."admin/index.php?page=channel&channel=".$getChannel."&action=delete'>delete</a><br />\n";
		}
	}
	echo "</fieldset>\n";
}
