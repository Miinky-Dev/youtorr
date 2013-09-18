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
$search=null;
$videos=null;
$channels=null;
if (!empty($_POST['search'])){
	$search=htmlentities($_POST['search']);
	$sqlSearch=str_replace(' ','%',$search);
	$sqlSearch='%'.$sqlSearch.'%';
	$sqlSearchUrl=escapeshellcmd($search);
	$sqlSearchUrl="%".$sqlSearchUrl."%";
	if($config['dbEngine']=="mysql") //Mysql doesn't like \?= in url
		$sqlSearchUrl=str_replace("\\","\\\\",$sqlSearchUrl);
	if($_GET['page']=='default' || $_GET['page']=='video'){
		$conn=$db->prepare("SELECT `videos`.`name`,`videos`.`torrent_file`,`channels`.`channel` FROM `videos` INNER JOIN `channels` 
		ON `channels`.`id`=`videos`.`id_channel` WHERE `videos`.`name` LIKE :search OR `videos`.`desc` LIKE :search OR `videos`.`url` LIKE :url");
		$conn->bindParam(':search',$sqlSearch);
		$conn->bindParam(':url',$sqlSearchUrl);
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
