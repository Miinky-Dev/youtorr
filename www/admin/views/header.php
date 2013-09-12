<?php
if(!defined('YOUTORR')){
	die(1);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body>
<a href='<?php echo $proto.$config['serverURL']."/index.php";?>'>front</a>
<a href='<?php echo $proto.$config['serverURL']."admin/index.php";?>'>home</a>
<?php
if($_SESSION['role']==100){
	echo '<a href="'.$proto.$config['serverURL'].'admin/index.php?page=admin">admin</a> ';
}
?>
<a href='<?php echo $proto.$config['serverURL']."admin/index.php?page=user";?>'>profile</a>
<a href='<?php echo $proto.$config['serverURL']."admin/index.php?logout=true";?>'>logout</a>
