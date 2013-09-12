<?php
if(!defined('YOUTORR')){
	die(1);
}
$search=null;
$videos=null;
$channels=null;
if (!empty($_POST['search'])){
	$search=htmlentities($_POST['search']);
	$sqlSearch=str_replace(' ','%',$search);
	$sqlSearch='%'.$sqlSearch.'%';
	if($_GET['page']=='default' || $_GET['page']=='video'){
		$conn=$db->prepare("SELECT `videos`.`name`,`videos`.`torrent_file`,`channels`.`channel` FROM `videos` INNER JOIN `channels` ON `channels`.`id`=`videos`.`id_channel` WHERE `videos`.`name`
		LIKE :search OR `videos`.`desc` LIKE :search");
		$conn->bindParam(':search',$sqlSearch);
		$conn->execute();
		$videos=$conn->fetchAll(PDO::FETCH_ASSOC);
	}
	if($_GET['page']=='default' || $_GET['page']=='channel'){
		$conn=$db->prepare("SELECT `id`,`channel` FROM `channels` WHERE `channel` LIKE :search");
		$conn->bindParam(':search',$sqlSearch);
		$conn->execute();
		$channels=$conn->fetchAll(PDO::FETCH_ASSOC);
	}
}
include('views/search.php');
