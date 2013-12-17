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
if(!empty($_POST['login']) && !empty($_POST['passwd'])){ #login 
	if(checkPassword($_POST['login'],$_POST['passwd'],$db)){
		$conn=$db->prepare("SELECT * FROM `users` WHERE `login` = :login");
		$conn->bindParam(':login',$_POST['login']);
		$conn->execute();
		$res = $conn->fetch(PDO::FETCH_ASSOC);
		$_SESSION['login']=$res['login'];
		$_SESSION['role']=$res['role'];
		$_SESSION['id']=$res['id'];
		$_SESSION['mail']=$res['mail'];
	}
}else{
	#update profile
	if(!empty($_POST['updateProfile'])){
		if(checkPassword($_SESSION['login'],$_POST['Password'],$db)){
			$mail=trim(htmlentities($_POST['mail']));
			if(!empty($_POST["mail"]) && $mail!=$_SESSION['mail']){ #update mail
				$conn=$db->prepare("UPDATE `users` SET `mail`=:mail WHERE `id`=:id");
				$conn->bindParam(':mail',$mail);
				$conn->bindParam(':id',$_SESSION['id']);
				$conn->execute();
				$_SESSION['mail']=$mail;
			}
			if(!empty($_POST['newPassword']) && !empty($_POST['newPassword2'])){ #update password
				if($_POST['newPassword']==$_POST['newPassword2']){
					updatePassword($db,$_POST['newPassword2']);
				}else{
					echo "passwords not match";
				}
			}
		}
	}
	$_SESSION['seed'] = bin2hex(mcrypt_create_iv(64, MCRYPT_DEV_URANDOM));
	include('views/user.php');	
}
?>
