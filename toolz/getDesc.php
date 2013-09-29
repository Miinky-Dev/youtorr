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

$conn=$db->prepare("SELECT * FROM videos");
$conn->execute();
$res = $conn->fetchAll(PDO::FETCH_ASSOC);
foreach($res as $video){
	if(!empty($video['desc']))
		continue;
	$curTimestamp=time();
	$logFile = "desc-".$video['id']."-$curTimestamp.log";
	$cmd = $config['pythonPath']." ".$config['youtubedlPath']." ".$video['url']." --get-description";
	if($config['verbose']){
		echo "Commande : $cmd\n";
		echo "Log file : $logFile\n";
	}
	$errors=launchProccess($cmd,$logFile);
	if(empty($errors)){
		$desc=file_get_contents($logFile);
		echo $video['name']." :\n";
		echo $desc."\n";
		$conn=$db->prepare("UPDATE `videos` SET `desc` = :desc WHERE `id` = :id");
		$desc=nl2br($desc);
		$conn->bindParam(':desc',$desc);
		$conn->bindParam(':id',$video['id']);
		$conn->execute();
	}else{
		echo $errors;
	}
	unlink($logFile);
}
?>
