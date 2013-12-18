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
include_once('admin/config.php');
include_once('admin/functions.php');
#Http or https ?
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
#whoami
$config['serverURL']=$_SERVER['SERVER_NAME'].'/';

$sql="SELECT `videos`.`name` , `videos`.`desc` , `videos`.`torrent_file` ,`videos`.`magnetlink` , `videos`.`provider` , `videos`.`url` , `videos`.`id` ,
`channels`.`channel`,`channels`.`url` AS channel_url FROM `videos`
INNER JOIN `channels` ON `videos`.`id_channel` = `channels`.`id` ";
if(!empty($_GET['channel'])){
	$channel=$_GET['channel'];
	$channelID=getChannelId($channel,$db);
	if(!empty($channelID)){
		$sql.=" WHERE `id_channel` = '".$channelID."'";
	}
}
$sql.=" ORDER BY `videos`.`id` DESC";
#echo $sql;
$conn=$db->prepare($sql);
$conn->execute();
$videos=$conn->fetchAll(PDO::FETCH_ASSOC);
#var_dump($videos);

header("Content-Type: application/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">'."\n";
echo '<atom:link href="http://dallas.example.com/rss.xml" rel="self" type="application/rss+xml" />'."\n";
echo '<channel>'."\n";
echo '<title>Youtube to torrent</title>'."\n";
echo "<link>".$config['serverURL']." </link>\n";

foreach($videos as $video){
	$url=str_replace('\\','',$video['url']);
	echo "<item>\n";
	echo "<title><![CDATA[".$video['name']."]]></title>\n";
	echo "<guid><![CDATA[".$config['serverURL']."index.php?page=video&video=".$video['id']."]]></guid>\n";
	echo "<description><![CDATA[ ";
	if(!empty($video['channel'])){
		echo "User <a href='".$video['channel_url']."'>".$video['channel']."</a> add a new video<br />";
	}
	echo "<a href='".$video['magnetlink']."'>Magnet</a><br />";
	echo "Torrent : <a href='".$proto.$config['serverURL'].$config['frontTorrentFileDir']."/".$video['torrent_file'].$config['torrentExt']."'>here</a>
	<br />\n"
	."Download on ".$video['provider']." at <a href='$url'>" .$url."</a><br />\n
	".$video['desc']."]]></description>";
	echo"</item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
?>
