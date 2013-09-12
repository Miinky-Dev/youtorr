<?php
if(!defined('YOUTORR')){
	die(1);
}
$videoID=0;
if(isset($_GET['video']) && !empty($_GET['video'])){
	$videoID=getVideoId($_GET['video'],$db);
}
$sql="SELECT `videos`.`id`,`videos`.`name`,`videos`.`url`,`videos`.`desc`,`videos`.`torrent_file`,`channels`.`channel` FROM `videos` LEFT JOIN `channels` ON `channels`.`id`=`videos`.`id_channel`";
if($videoID!=0){
		$sql.=" WHERE `videos`.`id` = :id";
}
$sql.=" ORDER BY `videos`.`id` DESC";
if($_GET['page']=='default' && $config['nbLastVideo'] > 0){
	//$sql.=" LIMIT 0, :limit";
	$sql.=" LIMIT 0, ".$config['nbLastVideo'];
}

$conn=$db->prepare($sql);
if($videoID!=0){
	$conn->bindParam(':id',$videoID);
}
/*if($_GET['page']=='default' && $config['nbLastVideo'] > 0){
	echo $sql;
	$conn->bindParam(':limit',$config['nbLastVideo']);
}*/

$conn->execute();
$videos=$conn->fetchAll(PDO::FETCH_ASSOC);

include('views/video.php');
