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
<fieldset><legend>Search engine</legend>
<form action="" method="post">
<input name="search" type="text" />
<input name="submit" type="submit" value="search" />
</form>
</fieldset>
<?php
//if (!empty($_POST['search'])){
//echo $search;
if (!empty($search)){
	echo "<fieldset><legend>Result for $search</legend>\n";
	if(count($videos)>0){
		echo "<fieldset><legend>Videos : </legend>\n";
		foreach($videos as $video){
			echo "<a href='".$proto.$config['serverURL'].$config['frontTorrentFileDir']."/".$video['torrent_file'].$config['torrentExt']."'>".$video['name']
			."</a><br />\n";
			if($config['httpDownload']){
				echo "<a href='".$proto.$config['serverURL'].$config['frontTorrentDataDir']."/".$video['channel']."/".$video['name']."'>Download now</a><br />\n";
			}
		}
		echo "</fieldset>\n";
	}
	if(count($channels)>0){
			echo "<fieldset><legend>Channels : </legend>\n";
			foreach($channels as $channel){
				echo "<a href='".$proto.$config['serverURL']."?channel=".$channel['id']."'>".$channel['channel']."</a><br />\n";
			}
			echo "</fieldset>\n";
	}
	echo "</fieldset>\n";
}
