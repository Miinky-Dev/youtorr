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
