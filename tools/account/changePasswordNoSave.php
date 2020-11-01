WARNING: SAVE DATA IS LINKED TO YOUR PASSWORD, YOU MIGHT ESSENTIALLY BREAK YOUR LOAD FUNCTIONALITY BY USING THIS INSTEAD OF <a href="changePassword.php">changePassword.php</a><br>

<?php
include "../../incl/lib/connection.php";
include_once "../../config/security.php";
require "../../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
include_once "../../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
$userName = $ep->remove($_POST["userName"]);
$oldPass = $_POST["oldPassword"];
$newPass = $_POST["newPassword"];
if($userName != "" AND $newPass != "" AND $oldPass != ""){
	$pass = $gp->isValidUsrname($userName, $oldPass);
	if ($pass == 1) {
		//creating pass hash
		$passHash = password_hash($newPass, PASSWORD_DEFAULT);
		$query = $db->prepare("UPDATE accounts SET password = :password, salt = :salt WHERE userName = :userName");	
		$query->execute([':password' => $passHash, ':userName' => $userName, ':salt' => $salt]);
		echo "Password changed. <a href='index.php'>Go back to account management</a>";
	}else{
		echo "Invalid old password or non-existent account. <a href='changePassword.php'>Try again</a>";
	}
}else{
	echo '<form action="changePasswordNoSave.php" method="post">Username: <input type="text" name="userName"><br>Old password: <input type="password" name="oldPassword"><br>New password: <input type="password" name="newPassword"><br><input type="submit" value="Change"></form>';
}
?>