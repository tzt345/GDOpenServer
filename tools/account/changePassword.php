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
		if ($cloudSaveEncryption == 1) {
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName");	
			$query->execute([':userName' => $userName]);
			$accountID = $query->fetchColumn();
			$saveData = file_get_contents("../../data/accounts/$accountID");
			if (file_exists("../../data/accounts/keys/$accountID")) {
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
		$passHash = password_hash($newPass, PASSWORD_DEFAULT);
		$query = $db->prepare("UPDATE accounts SET password = :password WHERE userName = :userName");	
		$query->execute([':password' => $passHash, ':userName' => $userName]);
		echo "Password changed. <a href='index.php'>Go back to account management.</a>";
	} else {
		echo "Invalid old password or non-existent account. <a href='changePassword.php'>Try again.</a>";
	}
} else {
	echo '<form action="changePassword.php" method="post">
		Username: <input type="text" name="userName" minlength=3 maxlength=15><br>
		Old password: <input type="password" name="oldPassword"><br>
		New password: <input type="password" name="newPassword" minlength=6 maxlength=20><br>
		<input type="submit" value="Change">
	</form>';
}
?>