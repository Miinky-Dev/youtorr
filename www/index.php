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
		include('views/channel.php');
		break;
	case 'video':
		include('core/video.php');
		include('views/video.php');
		break;
	default:
		$_GET['page']='default';
		include('core/default.php');
		include('views/channel.php');
		include('views/video.php');
		break;
}
include('views/footer.php');
?>

