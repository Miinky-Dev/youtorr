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
if(!$config['enableAPI'])
	die();

function formatChannel($channels){
	$result ='';
	if(!empty($_GET['channel']) && count($channels) > 1)
		echo "False";
	else{
		if($_GET['format']=='txt'){
			if(!empty($_GET['channel'])){
				foreach($channels[0]['videos'] as $video){
					echo $video['name']."<br />";
				}
			}else{
				foreach($channels as $channel)
					$result.=$channel['channel']."<br/>";
			}
		}else{
			//echo json_encode($channels[0]);
			$result = array ();
			if(!empty($_GET['channel'])){
				echo count($channels)."<br />";
				foreach($channels[0]['videos'] as $video){
					array_push($result,$video['name'],"plop");
				}
			}else{
				foreach($channels as $channel){
					array_push($result,array ($channel['channel'] => $channel['url']));
				}
			}
			$result=json_encode($result);
		}
	}
	echo $result;
}

function help(){
?>
this is youtorr api, you can get info from this youtorr instance.<br />
Default output is in json, you can have in text with format=txt (one day maybe csv and/or xml will be supported)
For now it's support only channel.
You can get channel list from this instance with page=channel
You can get all videos in this server from a channel with page=channel&amp;channel=ChannelName
If Channel doesn't exist, api will answer you : false

<?php
}

if(empty($_GET['page'])){
	$_GET['page']='default';
}
$_GET['default'] = empty($_GET['default']) ? 'default' : $_GET['default'];
$_GET['format'] = empty($_GET['format']) ? 'json' : $_GET['format'];
#$_GET['channel'] = ( $channel > 0) ? $_GET['channel'] : '' ;
switch($_GET['page']){
	case 'channel':
		include('core/channel.php');
		formatChannel($channels);
		break;
	default:
		$_GET['page']='default';
		help();
		break;
}
