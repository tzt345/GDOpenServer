<?php
chdir(__DIR__);
require "../lib/connection.php";
require "../../config/chests.php";
require_once "../lib/XORCipher.php";
$XORCipher = new XORCipher();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/generateHash.php";
$generateHash = new generateHash();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
if (empty($_POST["accountID"]) OR empty($_POST["udid"]) OR empty($_POST["chk"]) OR !isset($_POST["rewardType"])) {
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
$chk = $ep->remove($_POST["chk"]);
$rewardType = $ep->remove($_POST["rewardType"]);
$query = $db->prepare("SELECT * FROM users WHERE extID = :id");
$query->execute([':id' => $id]);
$result = $query->fetchAll();
if ($query->rowCount() == 0) {
	$query = $db->prepare("INSERT INTO users (isRegistered, extID) VALUES (:register, :id)");
	$query->execute([':register' => $register, ':id' => $id]);
	$query = $db->prepare("SELECT * FROM users WHERE extID = :id");
	$query->execute([':id' => $id]);
	$result = $query->fetchAll();
}
$user = $result[0];
$userID = $user["userID"];
$chk = $XORCipher->cipher(base64_decode(substr($chk, 5)), 59182);
//rewards
//Time left
$currentTime = time() + 100;
$chest1time = $user["chest1time"];
$chest1count = $user["chest1count"];
$chest2count = $user["chest2count"];
$chest2time = $user["chest2time"];
$chest1diff = $currentTime - $chest1time;
$chest2diff = $currentTime - $chest2time;
// adding rewards
$chest1stuff = rand($chest1minOrbs, $chest1maxOrbs) . "," . rand($chest1minDiamonds, $chest1maxDiamonds) . "," . rand($chest1minShards, $chest1maxShards) . "," . rand($chest1minKeys, $chest1maxKeys);
$chest2stuff = rand($chest2minOrbs, $chest2maxOrbs) . "," . rand($chest2minDiamonds, $chest2maxDiamonds) . "," . rand($chest2minShards, $chest2maxShards) . "," . rand($chest2minKeys, $chest2maxKeys);
$chest1left = max(0, $chest1wait - $chest1diff);
$chest2left = max(0, $chest2wait - $chest2diff);
//reward claiming
if ($rewardType == 2 ){
	if ($chest2left != 0) {
		exit("-1");
	}
	$chest2count++;
	$query = $db->prepare("UPDATE users SET chest2count = :chest2count, chest2time = :currenttime WHERE userID = :userID");	
	$query->execute([':chest2count' => $chest2count, ':userID' => $userID, ':currenttime' => $currentTime]);
	$chest2left = $chest2wait;
} elseif ($rewardType == 1) {
	if ($chest1left != 0) {
		exit("-1");
	}
	$chest1count++;
	$query = $db->prepare("UPDATE users SET chest1count = :chest1count, chest1time = :currenttime WHERE userID = :userID");	
	$query->execute([':chest1count' => $chest1count, ':userID' => $userID, ':currenttime' => $currentTime]);
	$chest1left = $chest1wait;
}
// encoding and sending reponse
$string = base64_encode($XORCipher->cipher("1:" . $userID . ":" . $chk . ":" . $udid . ":" . $accountID . ":" . $chest1left . ":" . $chest1stuff . ":" . $chest1count . ":" . $chest2left . ":" . $chest2stuff . ":" . $chest2count . ":" . $rewardType, 59182));
$string = str_replace("/", "_", $string);
$string = str_replace("+", "-", $string);
$hash = $generateHash->genSolo4($string);
echo "SaKuJ" . $string . "|" . $hash;
?>
