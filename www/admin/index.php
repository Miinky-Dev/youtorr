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
include_once('config.php');
include_once('functions.php');
session_start ();
$config['serverURL']=$_SERVER['HTTP_HOST'].'/'.$config['sitePath'];
#Http or https ?
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
if($proto!="https://"){
	#echo "https://".$config['serverURL']."admin/";
	if($config['forceHTTPS']){
		header('Location: https://'.$config['serverURL'].'admin/');
	}
	echo "https is not active, you should use <a href='https://".$config['serverURL']."/admin/index.php'>https</a> for login<br />";
}
#whoami
if(isset($_GET['logout']) && $_GET['logout']==true){
	session_unset();
	session_destroy();
	header('location : '.$proto.$config['serverURL']);
}

if(!empty($_POST['login']) && !empty($_POST['passwd'])){
	include('core/user.php');
}
if(empty($_SESSION['login'])){
	include('views/login.php');
}else{
	if(empty($_GET['page'])){
		$_GET['page']='default';
	}

	include('views/header.php');

	switch($_GET['page']){
		case 'admin'   :
			include('core/admin.php');
			break;
		case 'user'    :
			include('core/user.php');
			break;
		case 'channel' :
			include('core/channel.php');
			break;
		default :
			include('core/default.php');
	}
}
include('views/footer.php');
?>
