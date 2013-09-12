<?php
if(!defined('YOUTORR')){
	die(1);
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body>
<?php
echo '<a href="'.$proto.$config['serverURL'].'index.php?page=video">All videos</a><br />
<a href="'.$proto.$config['serverURL'].'index.php?page=channel">All channels</a><br />
<a href="'.$proto.$config['serverURL'].'index.php">Home page</a><br />';
if (!empty($_SESSION['id'])){
	echo '<a href="'.$proto.$config['serverURL'].'admin/index.php">Member</a><br />';
}else{
	echo '<a href="'.$proto.$config['serverURL'].'admin/index.php">Login</a><br />';
}
