<?php
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

	include('views/user.php');	
}
?>
