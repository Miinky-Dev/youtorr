<?php

include_once('admin/config.php');
include_once('admin/functions.php');
session_start ();


#Http or https ?
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
#whoami
$config['serverURL']=$_SERVER['SERVER_NAME'].'/'.$config['sitePath'];

if(empty($_GET['page'])){
	$_GET['page']='default';
}
include_once('views/header.php');

include_once('core/search.php');
switch($_GET['page']){
	case 'channel':
		include('core/channel.php');
		break;
	case 'video':
		include('core/video.php');
		break;
	default:
		$_GET['page']='default';
		include('core/default.php');
		break;
}
include('views/footer.php');
?>

