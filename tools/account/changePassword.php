<?php
include "../../incl/lib/connection.php";
include_once "../../config/security.php";
require "../../incl/lib/generatePass.php";
$generatePass = new generatePass();
require_once "../../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
include_once "../../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
if(isset($_POST["userName"]) AND isset($_POST["oldPassword"]) AND isset($_POST["newPassword"])){
	$userName = $ep->remove($_POST["userName"]);
	$oldPass = $_POST["oldPassword"];
	$newPass = $_POST["newPassword"];
	$salt = "";
	if ($gp->isValidUsrname($userName, $newPass)) {
		exit("This is already your password. <a href='changePassword.php'>Try again.</a>");
	}
	$pass = $gp->isValidUsrname($userName, $oldPass);
	if ($pass == 1) {
		if($cloudSaveEncryption == 1){
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName");	
			$query->execute([':userName' => $userName]);
			$accountID = $query->fetchColumn();
			$saveData = file_get_contents("../../data/accounts/$accountID");
			if(file_exists("../../data/accounts/keys/$accountID")){
				$protected_key_encoded = file_get_contents("../../data/accounts/keys/$accountID");
				$protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded);
				$user_key = $protected_key->unlockKey($oldPass);
				try {
					$saveData = Crypto::decrypt($saveData, $user_key);
				} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
					exit("Your cloud save file is corrupted. Use <a href='changePasswordNoSave.php'>this tool</a> instead.");	
				}
				$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($newPass);
				$protected_key_encoded = $protected_key->saveToAsciiSafeString();
				$user_key = $protected_key->unlockKey($newPass);
				$saveData = Crypto::encrypt($saveData, $user_key);
				file_put_contents("../../data/accounts/$accountID", $saveData);
				file_put_contents("../../data/accounts/keys/$accountID", $protected_key_encoded);
			}
		}
		//creating pass hash
		$passHash = password_hash($newpass, PASSWORD_DEFAULT);
		$query = $db->prepare("UPDATE accounts SET password = :password, salt = :salt WHERE userName = :userName");	
		$query->execute([':password' => $passHash, ':userName' => $userName, ':salt' => $salt]);
		echo "Password changed. <a href='..'>Go back to tools</a>";
	}else{
		echo "Invalid old password or non-existent account. <a href='changePassword.php'>Try again</a>";
	}
}else{
	echo '<form action="changePassword.php" method="post">
	Username: <input type="text" name="userName"><br>
	Old password: <input type="password" name="oldPassword"><br>
	New password: <input type="password" name="newPassword"><br>
	<input type="submit" value="Change"></form>';
}
?>