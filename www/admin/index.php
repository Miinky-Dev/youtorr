<?php
include_once('config.php');
include_once('functions.php');
session_start ();
$config['serverURL']=$_SERVER['HTTP_HOST'].'/'.$config['sitePath'];
#Http or https ?
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
if($proto!="https://"){
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