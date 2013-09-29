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
include_once('../www/admin/config.php');
include_once('../www/admin/functions.php');

$conn=$db->prepare("SELECT * FROM channels");
$conn->execute();
$channels=$conn->fetchAll(PDO::FETCH_ASSOC);
foreach($channels as $channel){
	echo $channel['channel']." :\n";
	$conn=$db->prepare("SELECT * FROM videos WHERE id_channel = :id");
	$conn->bindParam(':id',$channel['id']);
	$conn->execute();
	$res=$conn->fetchAll(PDO::FETCH_ASSOC);
	$zip = new ZipArchive;
	if ($zip->open($config['torrentFileDir']."/".$config['zipPrefix'].$channel['channel'].'.zip', ZipArchive::CREATE) === TRUE) {
		if($zip->addEmptyDir($channel['channel'])){
			foreach($res as $video){
				echo $config['torrentFileDir']."/".$video['torrent_file'].$config['torrentExt'],$channel['channel']."/".$video['torrent_file']."\n";
				$zip->addFile($config['torrentFileDir']."/".$video['torrent_file'].$config['torrentExt'],$channel['channel']."/".$video['torrent_file']);
			}
		}
		echo "zip contain " . $zip->numFiles." files.\n";
		$zip->close();
	}else{
		echo "Fail";
	}
}
?>
