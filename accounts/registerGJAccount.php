<?php
include "../incl/lib/connection.php";
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
include "../config/security.php";
if($_POST["userName"] != ""){
	if ($onlyWebRegistration == 1) {
		exit("-1");
	}
	//here im getting all the data
	$userName = $ep->remove($_POST["userName"]);
	$password = $ep->remove($_POST["password"]);
	$email = $ep->remove($_POST["email"]);
	//checking if name is taken
	$query2 = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
	$query2->execute([':userName' => $userName]);
	$regusrs = $query2->fetchColumn();
	if ($regusrs > 0) {
		echo "-2";
	}else{
		$hashpass = password_hash($password, PASSWORD_DEFAULT);
		$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey)
		VALUES (:userName, :password, :email, '', :time, '')");
		$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
		echo "1";
	}
}
?>