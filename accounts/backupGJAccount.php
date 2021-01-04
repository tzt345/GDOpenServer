<?php
chdir(__DIR__);
set_time_limit(0);
ini_set("memory_limit", "128M");
ini_set("post_max_size", "50M");
ini_set("upload_max_filesize", "50M");
require "../config/security.php";
require "../incl/lib/connection.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
require_once "../incl/lib/generatePass.php";
$gp = new generatePass();
require_once "../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../incl/lib/defuse-crypto.phar";
use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
//here im getting all the data
$userName = $ep->remove($_POST["userName"]);
$password = $_POST["password"];
$saveData = $ep->remove($_POST["saveData"]);
$pass = $gp->isValidUsrname($userName, $password);
if ($pass == 1) {
	$saveDataArr = explode(";", $saveData); //splitting ccgamemanager and cclocallevels
	$saveData = str_replace("-", "+", $saveDataArr[0]); //decoding
	$saveData = str_replace("_", "/", $saveData);
	$saveData = base64_decode($saveData);
	$saveData = gzdecode($saveData);
	$orbs = explode("</s><k>14</k><s>", $saveData)[1];
	$orbs = explode("</s>", $orbs)[0];
	$lvls = explode("<k>GS_value</k>", $saveData)[1];
	$lvls = explode("</s><k>4</k><s>", $lvls)[1];
	$lvls = explode("</s>", $lvls)[0];
	$protected_key_encoded = "";
	if ($cloudSaveEncryption == 0) {
		$saveData = str_replace("<k>GJA_002</k><s>" . $password . "</s>", "<k>GJA_002</k><s>not the actual password</s>", $saveData); //replacing pass
		$saveData = gzencode($saveData); //encoding back
		$saveData = base64_encode($saveData);
		$saveData = str_replace("+", "-", $saveData);
		$saveData = str_replace("/", "_", $saveData);
		$saveData = $saveData . ";" . $saveDataArr[1]; //merging ccgamemanager and cclocallevels
	} else {
		$saveData = $ep->remove($_POST["saveData"]);
		$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
		$protected_key_encoded = $protected_key->saveToAsciiSafeString();
		$user_key = $protected_key->unlockKey($password);
		$saveData = Crypto::encrypt($saveData, $user_key);
	}
	$accountID = $gs->getAccountIDFromName($userName);
	file_put_contents("../data/accounts/$accountID", $saveData);
	file_put_contents("../data/accounts/keys/$accountID", $protected_key_encoded);
	$query = $db->prepare("SELECT extID FROM users WHERE userName = :userName LIMIT 1");
	$query->execute([':userName' => $userName]);
	$result = $query->fetchAll();
	$result = $result[0];
	$extID = $result["extID"];
	$query = $db->prepare("UPDATE users SET orbs = :orbs, completedLvls = :lvls WHERE extID = :extID");
	$query->execute([':orbs' => $orbs, ':extID' => $extID, ':lvls' => $lvls]);
	echo "1";
} else {
	echo "-1";
}
?>