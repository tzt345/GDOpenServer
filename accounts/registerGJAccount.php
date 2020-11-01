<?php
include "../incl/lib/connection.php";
include "../config/security.php";
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
if ($onlyWebRegistration == 1) {
	exit("-1");
}
if($_POST["userName"] != ""){
	//here im getting all the data
	$userName = $ep->remove($_POST["userName"]);
	$password = $_POST["password"];
	$email = $ep->remove($_POST["email"]);
	//checking if name is taken
	$query2 = $db->prepare("SELECT count(*) FROM accounts WHERE userName LIKE :userName");
	$query2->execute([':userName' => $userName]);
	$regusrs = $query2->fetchColumn();
	if ($regusrs > 0) {
		echo "-2";
	}else{
		$hashpass = password_hash($password, PASSWORD_DEFAULT);
		if ($accountVerification == 2) {
			require "../incl/lib/mainLib.php";
			$ss = new mainLib();
			require "../incl/email/sendMail.php";
			$secret = $ss->randomString(16);
			$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified, verifySecret) VALUES (:userName, :password, :email, '', :time, '', 0, :secret)");
			$query->execute([':userName' => $username, ':password' => $hashpass, ':email' => $email, ':time' => time(), ':secret' => $secret]);
			$accountID = $db->lastInsertId();
			sendVerificationMail($email, $secret, $accountID);
		} elseif ($accountVerification == 1) {
			$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey, isVerified) VALUES (:userName, :password, :email, '', :time, '', 0)");
			$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
		} else {
			$query = $db->prepare("INSERT INTO accounts (userName, password, email, saveData, registerDate, saveKey) VALUES (:userName, :password, :email, '', :time, '')");
			$query->execute([':userName' => $userName, ':password' => $hashpass, ':email' => $email, ':time' => time()]);
		}
		echo "1";
	}
} else {
	echo "-1";
}
?>