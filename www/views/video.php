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
foreach($videos as $video){
	$url=str_replace('\\','',$video['url']);
	echo '<fieldset><legend><a href="'.$proto.$config['serverURL'].'index.php?page=video&video='.$video['id'].'">
	'.$video['name'].'</a></legend>
	'.$video['channel'].'<br />
	&nbsp;<a href="'.$video['magnetlink'].'">magnet</a>&nbsp<a href="'.$proto.$config['serverURL'].$config['frontTorrentFileDir'].'/'.$video['torrent_file'].$config['torrentExt'].'">Torrent file</a>&nbsp;';
	if($config['httpDownload']){
		echo '<a href="'.$config['frontTorrentDataDir'].'/'.$video['name'].'">http link</a>&nbsp;';
	}
	echo '<a href="'.$url.'">Original link</a><br />
	'.nl2br($video['desc']).'
	</fieldset>
	';
}
