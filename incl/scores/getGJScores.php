<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/mainLib.php";
$gs = new mainLib();
if (!empty($_POST["accountID"]) AND $_POST["accountID"] != "0") {
	$extID = $ep->remove($_POST["accountID"]);
} elseif (!empty($_POST["udid"]) AND !is_numeric($_POST["udid"])) {
	$extID = $ep->remove($_POST["udid"]);
	if (is_numeric($extID)) {
		exit("-1");
	}
} else {
	exit("-1");
}

$stars = 0;
$count = 0;
$xi = 0;
$lbstring = "";
$type = $ep->remove($_POST["type"]);
if ($type == "top" OR $type == "creators" OR $type == "relative") {
	if ($type == "top") {
		$query = "SELECT * FROM users WHERE isBanned = 0 AND isLeaderboardBanned = 0 AND isRegistered = 1 AND stars > 0 ORDER BY stars DESC LIMIT 100";
	}
	if ($type == "creators") {
		$query = "SELECT * FROM users WHERE isBanned = 0 AND isLeaderboardBanned = 0 AND isCreatorBanned = 0 AND isRegistered = 1 ORDER BY creatorPoints DESC LIMIT 100";
	}
	if ($type == "relative") {
		$query = $db->prepare("SELECT * FROM users WHERE extID = :extID");
		$query->execute([':extID' => $extID]);
		$result = $query->fetchAll();
		$user = $result[0];
		$stars = $user["stars"];
		if (!empty($_POST["count"])) {
			$count = $ep->number($_POST["count"]);
		} else {
			$count = 50;
		}
		$count = floor($count / 2);
		$query = "SELECT	A.* FROM	(
			(
				SELECT * FROM users
				WHERE stars <= :stars
				AND isLeaderboardBanned = 0
				AND isRegistered = 1
				ORDER BY stars DESC
				LIMIT $count
			)
			UNION
			(
				SELECT * FROM users
				WHERE stars >= :stars
				AND isLeaderboardBanned = 0
				AND isRegistered = 1
				ORDER BY stars ASC
				LIMIT $count
			)
		) as A
		ORDER BY A.stars DESC";
	}
	$query = $db->prepare($query);
	$query->execute([':stars' => $stars, ':count' => $count]);
	$result = $query->fetchAll();
	if ($type == "relative") {
		$user = $result[0];
		$extid = $user["extID"];
		$query = $db->prepare("SET @rownum := 0;");
		$query->execute();
		$f = "SELECT rank, stars FROM (
		SELECT @rownum := @rownum + 1 AS rank, stars, extID
		FROM users WHERE isLeaderboardBanned = 0 AND isRegistered = 1 ORDER BY stars DESC
		) as result";
		$query = $db->prepare($f);
		$query->execute([':extid' => $extid]);
		$leaderboard = $query->fetchAll();
		$leaderboard = $leaderboard[0];
		$xi = $leaderboard["rank"] - 1;
	}
	foreach ($result as &$user) {
		$extid = $user["extID"];
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":13:" . $user["coins"] . ":17:" . $user["userCoins"] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $extid . ":3:" . $user["stars"] . ":8:" . round($user["creatorPoints"], 0, PHP_ROUND_HALF_DOWN) . ":4:" . $user["demons"] . ":7:" . $extid . ":46:" . $user["diamonds"] . "|";
	}
}
if ($type == "friends") {
	$query = $db->prepare("SELECT * FROM friendships WHERE person1 = :extID OR person2 = :extID");
	$query->execute([':extID' => $extID]);
	$result = $query->fetchAll();
	$people = "";
	foreach ($result as &$friendship) {
		if ($friendship["person1"] == $extID) {
			$person = $friendship["person2"];
		} else {
			$person = $friendship["person1"];
		}
		$people .= "," . $person;
	}
	$query = $db->prepare("SELECT * FROM users WHERE extID IN (:extID $people ) ORDER BY stars DESC");
	$query->execute([':extID' => $extID]);
	$result = $query->fetchAll();
	foreach ($result as &$user) {
		$extid = $user["extID"];
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":13:" . $user["coins"] . ":17:" . $user["userCoins"] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $extid . ":3:" . $user["stars"] . ":8:" . round($user["creatorPoints"], 0, PHP_ROUND_HALF_DOWN) . ":4:" . $user["demons"] . ":7:" . $extid . ":46:" . $user["diamonds"] . "|";
	}
}
if ($type == "week") { // By Absolute, did some edits
	$starsgain = array();
	$xi = 0;
	$query = $db->prepare("SELECT * FROM actions WHERE type = 9 AND timestamp > :time");
	$query->execute([':time' => strtotime("last monday")]);
	$result = $query->fetchAll();
	foreach ($result as &$gain) {
		if (!empty($starsgain[$gain["account"]])) {
			$starsgain[$gain["account"]] += $gain["value"];
		} else {
			$starsgain[$gain["account"]] = $gain["value"];
		}
		if (!empty($coinsgain[$gain["account"]])) {
			$coinsgain[$gain["account"]] += $gain["value2"];
		} else {
			$coinsgain[$gain["account"]] = $gain["value2"];
		}
		if (!empty($demonsgain[$gain["account"]])) {
			$demonsgain[$gain["account"]] += $gain["value3"];
		} else {
			$demonsgain[$gain["account"]] = $gain["value3"];
		}
	}
	arsort($starsgain);
	foreach ($starsgain as $userID => $stars) {
		if ($stars == 0 OR $xi >= 100) {
			break;
		}
		$query = $db->prepare("SELECT userName, icon, color1, color2, iconType, special, extID FROM users WHERE userID = :userID");
		$query->execute([':userID' => $userID]);
		$user = $query->fetchAll()[0];
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $userID . ":4:" . $demonsgain[$userID] . ":13:" . $coinsgain[$userID] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $user["extID"] . ":3:" . $stars . ":7:" . $user["extID"] . "|";
	}
}
if ($lbstring == "") {
	exit("-1");
}
$lbstring = substr($lbstring, 0, -1);
echo $lbstring;
?>
