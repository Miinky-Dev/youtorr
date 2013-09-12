<?php
include_once('admin/config.php');
include_once('admin/functions.php');
$db=null;
try { #Connect to the db
	if($config['dbEngine']=="sqlite"){
		$db = new PDO('sqlite:admin/'.$config['dbName']);
	}elseif($config['dbEngine']=="mysql"){
		$db = new PDO('mysql:host='.$config['dbHost'].';port='.$config['dbPort'].';dbname='.$config['dbName'],$config['dbUser'],$config['dbPassword']);
	}elseif($config['dbEngine']=="postgre"){
		echo $config['dbEngine']." not supported yet";
	}
} catch (PDOException $e ) {
	print "Erreur!: " . $e->getMessage() . "<br/>";
	die();
}
#Http or https ?
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
#whoami
$config['serverURL']=$_SERVER['SERVER_NAME'].'/';

$sql="SELECT `name`,`desc`,`torrent_file`,`provider`,`url`,`id` FROM `videos`";
if(!empty($_GET['channel'])){
	$channel=$_GET['channel'];
	$channelID=getChannelId($channel,$db);
	if(!empty($channelID)){
		$sql.=" WHERE `id_channel` = '".$channelID."'";
	}
}
$sql.=" ORDER BY `id` DESC";
$conn=$db->prepare($sql);
$conn->execute();
$videos=$conn->fetchAll(PDO::FETCH_ASSOC);

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
	//echo "<link><![CDATA[".$config['serverURL'].$config['frontTorrentFileDir']."/".$video['torrent_file'].$config['torrentExt']."]]></link>\n";
	echo "<description><![CDATA[ Torrent : <a href='".$proto.$config['serverURL'].$config['frontTorrentFileDir']."/".$video['torrent_file'].$config['torrentExt']."'>here</a><br />\n"
	."Download on ".$video['provider']." at <a href='$url'>" .$url."</a><br />\n
	".$video['desc']."]]></description>";
	//echo "<content><![CDATA[Download on ".$video['provider']." at " .$video['url']."]]></content>";
	echo"</item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
?>
