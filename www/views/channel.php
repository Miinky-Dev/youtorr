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
foreach($channels as $channel){
	echo '<fieldset><legend><a href="'.$proto.$config['serverURL'].'index.php?page=channel&channel='.$channel['channel'].'">'.$channel['channel'].'</a> Total videos : '
	.$channel['nbVideo'].' <a href="'
	.$proto.$config['serverURL'].'rss.php?channel='.$channel['channel'].'">RSS</a> <a href="'.$channel['url'].'">Original Channel</a> ';
	if($config['zipUserTorrent'] && file_exists($config['frontTorrentFileDir']."/".$config['zipPrefix'].$channel['channel'].".zip")){
		echo '<a href="'.$proto.$config['serverURL'].$config['frontTorrentFileDir'].'/'.$config['zipPrefix'].$channel['channel'].".zip".'">All torrent files</a>';
	}
	echo '</legend>';
	if(!empty($channel['size'])){
		echo 'Estimed size : ' . $channel['size'].'<br />';
	}
	foreach($channel['videos'] as $video){
		echo '<a href="'.$proto.$config['serverURL'].'index.php?page=video&video='.$video['id'].'">'.$video['name']
		.'</a><br />
		';
	}
	echo '</fieldset>
	';
}
