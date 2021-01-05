<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
//here im getting all the data
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult != 1 OR empty($_POST["levelID"])) {
	exit("-1");
}
$levelID = $ep->remove($_POST["levelID"]);
$percent = $ep->remove($_POST["percent"]);
$uploadDate = time();
if (!empty($_POST["s1"])) {
	$attempts = $_POST["s1"] - 8354;
} else {
	$attempts = 0;
}
if (!empty($_POST["s9"])) {
	$coins = $_POST["s9"] - 5819;
} else {
	$coins = 0;
}

//UPDATING SCORE
if ($percent > 100) {
	$query = $db->prepare("UPDATE users SET isLeaderboardBanned = 1 WHERE userID = :userID");
	$query->execute([':userID' => $userID]);
	exit("-1");
}
$userID = $gs->getUserID($accountID);
$query2 = $db->prepare("SELECT percent FROM levelscores WHERE accountID = :accountID AND levelID = :levelID");
$query2->execute([':accountID' => $accountID, ':levelID' => $levelID]);
$oldPercent = $query2->fetchColumn();
if ($query2->rowCount() == 0) {
	$query = $db->prepare("INSERT INTO levelscores (accountID, levelID, percent, uploadDate, coins, attempts) VALUES (:accountID, :levelID, :percent, :uploadDate, :coins, :attempts)");
} elseif ($oldPercent <= $percent) {
	$query = $db->prepare("UPDATE levelscores SET percent = :percent, uploadDate = :uploadDate, coins = :coins, attempts = :attempts WHERE accountID = :accountID AND levelID = :levelID");
}
$query->execute([':accountID' => $accountID, ':levelID' => $levelID, ':percent' => $percent, ':uploadDate' => $uploadDate, ':coins' => $coins, ':attempts' => $attempts]);

//GETTING SCORES
if (!isset($_POST["type"])) {
	$type = 1;
} else {
	$type = $_POST["type"];
}
switch ($type) {
	case 0:
		$friends = $gs->getFriends($accountID);
		$friends[] = $accountID;
		$friends = implode(",", $friends);
		$query2 = $db->prepare("SELECT accountID, uploadDate, percent, coins FROM levelscores WHERE levelID = :levelID AND accountID IN ($friends) ORDER BY percent DESC");
		$query2args = [':levelID' => $levelID];
		break;
	case 1:
		$query2 = $db->prepare("SELECT accountID, uploadDate, percent, coins FROM levelscores WHERE levelID = :levelID ORDER BY percent DESC");
		$query2args = [':levelID' => $levelID];
		break;
	case 2:
		$query2 = $db->prepare("SELECT accountID, uploadDate, percent, coins FROM levelscores WHERE levelID = :levelID AND uploadDate > :time ORDER BY percent DESC");
		$query2args = [':levelID' => $levelID, ':time' => $uploadDate - 604800];
		break;
	default:
		exit("-1");
}
$query2->execute($query2args);
$result = $query2->fetchAll();
$place = 1;
foreach ($result as &$score) {
	$extID = $score["accountID"];
	$query2 = $db->prepare("SELECT userName, userID, icon, color1, color2, iconType, special, extID, FROM users WHERE extID = :extID AND isLeaderboardBanned = 0 LIMIT 1");
	$query2->execute([':extID' => $extID]);
	$user = $query2->fetchAll();
	$time = $gs->makeTime($score["uploadDate"]);
	$lvlscorestring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $user["extID"] . ":3:" . $score["percent"] . ":6:" . $place . ":13:" . $score["coins"] . ":42:" . $time . "|"; */
	$place++;
}
$lvlscorestring = substr($msgstring, 0, -1);
echo $lvlscorestring;
?>