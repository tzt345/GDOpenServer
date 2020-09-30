<?php
include "../../incl/lib/connection.php";
include_once "../../config/security.php";
require "../../incl/lib/generatePass.php";
require_once "../../incl/lib/exploitPatch.php";
include_once "../../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
$ep = new exploitPatch();
if(!empty($_POST["userName"]) AND !empty($_POST["oldpassword"]) AND !empty($_POST["newpassword"])){
	$userName = $ep->remove($_POST["userName"]);
	$oldpass = $_POST["oldpassword"];
	$newpass = $_POST["newpassword"];
	$salt = "";
	$generatePass = new generatePass();
	if ($generatePass->isValidUsrname($userName, $newpass)) {
		exit("This is already your password. <a href='changePassword.php'>Try again.</a>");
	}
	$pass = $generatePass->isValidUsrname($userName, $oldpass);
	if ($pass == 1) {
		if($cloudSaveEncryption == 1){
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
			$query->execute([':userName' => $userName]);
			$accountID = $query->fetchColumn();
			$saveData = file_get_contents("../../data/accounts/$accountID");
			if(file_exists("../../data/accounts/keys/$accountID")){
				$protected_key_encoded = file_get_contents("../../data/accounts/keys/$accountID");
				$protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded);
				$user_key = $protected_key->unlockKey($oldpass);
				try {
					$saveData = Crypto::decrypt($saveData, $user_key);
				} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
					exit("Your cloud save file is corrupted. Use <a href='changePasswordNoSave.php'>this tool</a> instead.");	
				}
				$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($newpass);
				$protected_key_encoded = $protected_key->saveToAsciiSafeString();
				$user_key = $protected_key->unlockKey($newpass);
				$saveData = Crypto::encrypt($saveData, $user_key);
				file_put_contents("../../data/accounts/$accountID",$saveData);
				file_put_contents("../../data/accounts/keys/$accountID",$protected_key_encoded);
			}
		}
		//creating pass hash
		$passhash = password_hash($newpass, PASSWORD_DEFAULT);
		$query = $db->prepare("UPDATE accounts SET password=:password, salt=:salt WHERE userName=:userName");	
		$query->execute([':password' => $passhash, ':userName' => $userName, ':salt' => $salt]);
		echo "Password changed. <a href='..'>Go back to tools</a>";
	}else{
		echo "Invalid old password or nonexistent account. <a href='changePassword.php'>Try again</a>";
	}
}else{
	echo '<form action="changePassword.php" method="post">Username: <input type="text" name="userName"><br>Old password: <input type="password" name="oldpassword"><br>New password: <input type="password" name="newpassword"><br><input type="submit" value="Change"></form>';
}
?>