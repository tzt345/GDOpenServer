<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$appendix = "";
$extid = $ep->number($_POST["targetAccountID"]);
if (!empty($_POST["accountID"]) AND !empty($_POST["gjp"])) {
	$gjp = $ep->remove($_POST["gjp"]);
	$me = $ep->number($_POST["accountID"]);
	$gjpresult = $GJPCheck->check($gjp, $me); //gjp check
	if ($gjpresult != 1) {
		exit("-1");
	}
} else {
	$me = 0;
}
//checking who has blocked him
$query = $db->prepare("SELECT count(*) FROM blocks WHERE (person1 = :extid AND person2 = :me) OR (person2 = :extid AND person1 = :me)");
$query->execute([':extid' => $extid, ':me' => $me]);
if ($query->fetchColumn() > 0) {
	exit("-1");
}
$query = $db->prepare("SELECT * FROM users WHERE extID = :extid");
$query->execute([':extid' => $extid]);
if ($query->rowCount() == 0) {
	exit("-1");
}
$user = $query->fetch();
//placeholders
if ($user["isCreatorBanned"] == 1) {
	$creatorPoints = 0;
} else {
	$creatorPoints = round($user["creatorPoints"], PHP_ROUND_HALF_DOWN);
}
// GET POSITION
$e = "SET @rownum := 0;";
$query = $db->prepare($e);
$query->execute();
/*$f = "SELECT rank FROM (
				  SELECT @rownum := @rownum + 1 AS rank, extID
				  FROM users WHERE isBanned = 0 AND gameVersion > 19 AND stars > 25 ORDER BY stars DESC
				  ) as result WHERE extID = :extid";*/
$query = $db->prepare("SELECT count(*) FROM users WHERE stars > :stars AND isLeaderboardBanned = 0"); //I can do this, since I already know the stars amount beforehand
$query->execute([':stars' => $user["stars"]]);
if ($query->rowCount() > 0) {
	$rank = $query->fetchColumn() + 1;
} else {
	$rank = 0;
}
//accinfo
$query = $db->prepare("SELECT youtubeurl, twitter, twitch, frS, mS, cS FROM accounts WHERE accountID = :extID");
$query->execute([':extID' => $extid]);
$accinfo = $query->fetch();
$reqsstate = $accinfo["frS"];
$msgstate = $accinfo["mS"];
$commentstate = $accinfo["cS"];
$badge = $gs->getMaxValuePermission($extid, "modBadgeLevel");
if ($me == $extid) {
	/* notifications */
	//friendreqs
	$query = $db->prepare("SELECT count(*) FROM friendreqs WHERE toAccountID = :me");
	$query->execute([':me' => $me]);
	$requests = $query->fetchColumn();
	//messages
	$query = $db->prepare("SELECT count(*) FROM messages WHERE toAccountID = :me AND isNew = 0");
	$query->execute([':me' => $me]);
	$pms = $query->fetchColumn();
	//friends
	$query = $db->prepare("SELECT count(*) FROM friendships WHERE (person1 = :me AND isNew2 = 1) OR (person2 = :me AND isNew1 = 1)");
	$query->execute([':me' => $me]);
	$friends = $query->fetchColumn();
	/* sending the data */
	//38,39,40 are notification counters
	//18 = enabled (0) or disabled (1) messaging
	//19 = enabled (0) disabled (1) friend requests
	//31 = isnt (0) or is (1) friend or (3) incoming request or (4) outgoing request
	$friendstate = 0;
	$appendix = ":38:" . $pms . ":39:" . $requests . ":40:" . $friends;
} else {
	/* friend state */
	$friendstate = 0;
	//check if INCOING friend request
	$query = $db->prepare("SELECT ID, comment, uploadDate FROM friendreqs WHERE accountID = :extid AND toAccountID = :me");
	$query->execute([':extid' => $extid, ':me' => $me]);
	$INCrequests = $query->rowCount();
	$INCrequestinfo = $query->fetch();
	if ($INCrequests > 0) {
		$uploadDate = $gs->makeTime($INCrequestinfo["uploadDate"]);
		$friendstate = 3;
	}
	//check if OUTCOMING friend request
	$query = $db->prepare("SELECT count(*) FROM friendreqs WHERE toAccountID = :extid AND accountID = :me");
	$query->execute([':extid' => $extid, ':me' => $me]);
	$OUTrequests = $query->fetchColumn();
	if ($OUTrequests > 0) {
		$friendstate = 4;
	}
	//check if friend ALREADY
	$query = $db->prepare("SELECT count(*) FROM friendships WHERE (person1 = :me AND person2 = :extID) OR (person2 = :me AND person1 = :extID)");
	$query->execute([':me' => $me, ':extID' => $extid]);
	$frs = $query->fetchColumn();
	if ($frs > 0) {
		$friendstate = 1;
	}
	/* sending the data */
	//$friendstate is :31:
	//$reqsstate is :19:
	if ($INCrequests > 0) {
		$appendix = ":32:" . $INCrequestinfo["ID"] . ":35:" . $INCrequestinfo["comment"] . ":37:" . $uploadDate;
	}
}
echo "1:" . $user["userName"] . ":2:" . $user["userID"] . ":13:" . $user["coins"] . ":17:" . $user["userCoins"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":3:" . $user["stars"] . ":46:" . $user["diamonds"] . ":4:" . $user["demons"] . ":8:" . $creatorPoints . ":18:" . $msgstate . ":19:" . $reqsstate . ":50:" . $commentstate . ":20:" . $accinfo["youtubeurl"] . ":21:" . $user["accIcon"] . ":22:" . $user["accShip"] . ":23:" . $user["accBall"] . ":24:" . $user["accBird"] . ":25:" . $user["accDart"] . ":26:" . $user["accRobot"] . ":28:" . $user["accGlow"] . ":43:" . $user["accSpider"] . ":47:" . $user["accExplosion"] . ":30:" . $rank . ":16:" . $user["extID"] . ":31:" . $friendstate . ":44:" . $accinfo["twitter"] . ":45:" . $accinfo["twitch"] . ":29:1:49:" . $badge . $appendix;
?>