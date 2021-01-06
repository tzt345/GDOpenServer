<?php
require "../incl/lib/connection.php";
require "../config/security.php";
require_once "../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
//here im getting all the data
$userName = $ep->remove($_GET["userName"]);
$password = $_GET["password"];
$pass = $gp->isValidUsrname($userName, $password);
if ($pass == 1) {
	$query = $db->prepare("SELECT accountID, saveData FROM accounts WHERE userName = :userName");
	$query->execute([':userName' => $userName]);
	$account = $query->fetch();
	$accountID = $account["accountID"];
	if (!is_numeric($accountID)) {
		exit("unknown account");
	}
	if (!file_exists("../data/accounts/$accountID")) {
			$saveData = $account["saveData"];
		if (substr($saveData, 0, 4) == "SDRz") {
			$saveData = base64_decode($saveData);
		}
	} else {
		$saveData = file_get_contents("../data/accounts/$accountID");
		if (file_exists("../data/accounts/keys/$accountID")) {
			if (substr($saveData, 0, 3) != "H4s") {
				$protected_key_encoded = file_get_contents("../data/accounts/keys/$accountID");
				$protected_key = KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded);
				$user_key = $protected_key->unlockKey($password);
				try {
					$saveData = Crypto::decrypt($saveData, $user_key);
				} catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
					exit("-2");	
				}
			}
		}
	}
	echo $saveData . ";21;30;a;a";
} else {
	echo "wrong pass";
}
?>