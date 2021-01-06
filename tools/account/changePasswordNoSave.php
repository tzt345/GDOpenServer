WARNING: SAVE DATA IS LINKED TO YOUR PASSWORD, YOU MIGHT ESSENTIALLY BREAK YOUR LOAD FUNCTIONALITY BY USING THIS INSTEAD OF <a href="changePassword.php">changePassword.php</a><br>

<?php
require "../../incl/lib/connection.php";
require "../../config/security.php";
require_once "../../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
if (!empty($_POST["userName"]) AND !empty($_POST["oldPassword"]) AND !empty($_POST["newPassword"])) {
	$userName = $ep->remove($_POST["userName"]);
	$oldPass = $_POST["oldPassword"];
	$newPass = $_POST["newPassword"];
	if ($gp->isValidUsrname($userName, $newPass)) {
		exit("This is already your password. <a href='changePassword.php'>Try again.</a>");
	}
	$pass = $gp->isValidUsrname($userName, $oldPass);
	if ($pass == 1) {
		//creating pass hash
		$passHash = password_hash($newPass, PASSWORD_DEFAULT);
		$query = $db->prepare("UPDATE accounts SET password = :password WHERE userName = :userName");	
		$query->execute([':password' => $passHash, ':userName' => $userName]);
		echo "Password changed. <a href='index.php'>Go back to account management.</a>";
	} else {
		echo "Invalid old password or non-existent account. <a href='changePassword.php'>Try again.</a>";
	}
} else {
	echo '<form action="changePasswordNoSave.php" method="post">
		Username: <input type="text" name="userName" minlength=3 maxlength=15><br>
		Old password: <input type="password" name="oldPassword"><br>
		New password: <input type="password" name="newPassword" minlength=6 maxlength=20><br>
		<input type="submit" value="Change">
	</form>';
}
?>