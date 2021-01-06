<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/XORCipher.php";
$XORCipher = new XORCipher();
require_once "../lib/generateHash.php";
$generateHash = new generateHash();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$usedids = array();
if (empty($_POST["accountID"]) OR empty($_POST["udid"]) OR empty($_POST["chk"])) {
	exit("-1");
}
$accountID = $ep->remove($_POST["accountID"]);
$udid = $ep->remove($_POST["udid"]);
if (is_numeric($udid)) {
	exit("-1");
}
if ($accountID != 0) {
	if (empty($_POST["gjp"])) {
		exit("-1");
	}
	$gjp = $ep->remove($_POST["gjp"]);
	$gjpresult = $GJPCheck->check($gjp, $accountID);
	if ($gjpresult != 1) {
		exit("-1");
	}
	$id = $accountID;
} else {
	$id = $udid;
}
$userID = $gs->getUserID($id);
$chk = $ep->remove($_POST["chk"]);
$chk = $XORCipher->cipher(base64_decode(substr($chk, 5)), 19847);
//Generating quest IDs
$from = strtotime('2000-12-17');
$today = time();
$difference = $today - $from;
$questID = floor($difference / 86400);
$questID = $questID * 3;
$quest1ID = $questID;
$quest2ID = $questID + 1;
$quest3ID = $questID + 2;
//Time left
$midnight = strtotime("tomorrow 00:00:00");
$timeleft = $midnight - $today;
$query = $db->prepare("SELECT type, amount, reward, name FROM quests");
$query->execute();
$result = $query->fetchAll();
if (empty($result[0]) OR empty($result[1]) OR empty($result[2])) {
	exit("-1");
}
shuffle($result);
//quests
$quest1 = $quest1ID . "," . $result[0]["type"] . "," . $result[0]["amount"] . "," . $result[0]["reward"] . "," . $result[0]["name"] . "";
$quest2 = $quest2ID . "," . $result[1]["type"] . "," . $result[1]["amount"] . "," . $result[1]["reward"] . "," . $result[1]["name"] . "";
$quest3 = $quest3ID . "," . $result[2]["type"] . "," . $result[2]["amount"] . "," . $result[2]["reward"] . "," . $result[2]["name"] . "";
$string = base64_encode($XORCipher->cipher("SaKuJ:" . $userID . ":" . $chk . ":" . $udid . ":" . $accountID . ":" . $timeleft . ":" . $quest1 . ":" . $quest2 . ":" . $quest3, 19847));
$hash = $generateHash->genSolo3($string);
echo "SaKuJ" . $string . "|" . $hash;
?>
