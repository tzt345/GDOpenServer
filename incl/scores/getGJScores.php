<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gameVersion = $ep->number($_POST["gameVersion"]);
if (empty($gameVersion)) {
	$sign = "< 20 AND gameVersion <> 0";
} else {
	$sign = "> 19";
}
if ($gameVersion >= 20) {
	if (isset($_POST["accountID"])) {
		$accountID = $ep->remove($_POST["accountID"]);
		$gjp = $ep->remove($_POST["gjp"]);
		$gjpresult = $GJPCheck->check($gjp, $accountID);
		if ($gjpresult != 1) {
			exit("-1");
		}
	} else {
		$accountID = $ep->remove($_POST["udid"]);
		if (!is_numeric($accountID)) {
			exit("-1");
		}
	}
} else {
	if (isset($_POST["accountID"]) AND $_POST["accountID"] != "0") {
		$id = $ep->remove($_POST["accountID"]);
	}
}

$stars = 0;
$count = 0;
$xi = 0;
$lbstring = "";
$date = date("d-m");
$type = $ep->remove($_POST["type"]);
if ($type == "top" OR $type == "creators" OR $type == "relative") {
	if ($type == "top") {
		$query = "SELECT * FROM users WHERE isBanned = 0 AND isLeaderboardBanned = 0 AND gameVersion $sign AND stars > 0 ORDER BY stars DESC LIMIT 100";
	}
	if ($type == "creators") {
		$query = "SELECT * FROM users WHERE isBanned = 0 AND isLeaderboardBanned = 0 AND isCreatorBanned = 0 ORDER BY creatorPoints DESC LIMIT 100";
	}
	if ($type == "relative") {
		$query = $db->prepare("SELECT * FROM users WHERE extID = :accountID");
		$query->execute([':accountID' => $accountID]);
		$result = $query->fetchAll();
		$user = $result[0];
		$stars = $user["stars"];
		if ($_POST["count"]) {
			$count = $ep->remove($_POST["count"]);
		} else {
			$count = 50;
		}
		$count = floor($count / 2);
		$query = "SELECT	A.* FROM	(
			(
				SELECT * FROM users
				WHERE stars <= :stars
				AND isBanned = 0
				AND isLeaderboardBanned = 0
				AND gameVersion $sign
				ORDER BY stars DESC
				LIMIT $count
			)
			UNION
			(
				SELECT * FROM users
				WHERE stars >= :stars
				AND isBanned = 0
				AND isLeaderboardBanned = 0
				AND gameVersion $sign
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
							SELECT @rownum := @rownum + 1 AS rank, stars, extID, isBanned
							FROM users WHERE isBanned = 0 AND isLeaderboardBanned = 0 AND gameVersion $sign ORDER BY stars DESC
							) as result WHERE extID = :extid";
		$query = $db->prepare($f);
		$query->execute([':extid' => $extid]);
		$leaderboard = $query->fetchAll();
		$leaderboard = $leaderboard[0];
		$xi = $leaderboard["rank"] - 1;
	}
	foreach ($result as &$user) {
		if (is_numeric($user["extID"])) {
			$extid = $user["extID"];
		} else {
			$extid = 0;
		}
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":13:" . $user["coins"] . ":17:" . $user["userCoins"] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $extid . ":3:" . $user["stars"] . ":8:" . round($user["creatorPoints"], 0, PHP_ROUND_HALF_DOWN) . ":4:" . $user["demons"] . ":7:" . $extid . ":46:" . $user["diamonds"] . "|";
	}
}
if ($type == "friends") {
	$query = $db->prepare("SELECT * FROM friendships WHERE person1 = :accountID OR person2 = :accountID");
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	$people = "";
	foreach ($result as &$friendship) {
		if ($friendship["person1"] == $accountID) {
			$person = $friendship["person2"];
		} else {
			$person = $friendship["person1"];
		}
		$people .= "," . $person;
	}
	$query = $db->prepare("SELECT * FROM users WHERE extID IN (:accountID $people ) ORDER BY stars DESC");
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	foreach($result as &$user) {
		if (is_numeric($user["extID"])) {
			$extid = $user["extID"];
		} else {
			$extid = 0;
		}
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":13:" . $user["coins"] . ":17:" . $user["userCoins"] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $extid . ":3:" . $user["stars"] . ":8:" . round($user["creatorPoints"], 0, PHP_ROUND_HALF_DOWN) . ":4:" . $user["demons"] . ":7:" . $extid . ":46:" . $user["diamonds"] . "|";
	}
}
if ($type == "week") {
	$starsgain = array();
	$time = time() - 604800;
	$xi = 0;
	$query = $db->prepare("SELECT * FROM actions WHERE type = 9 AND timestamp > :time");
	$query->execute([':time' => $time]);
	$result = $query->fetchAll();
	foreach ($result as &$gain) {
		if (!empty($starsgain[$gain["account"]])) {
			$starsgain[$gain["account"]] += $gain["value"];
		} else {
			$starsgain[$gain["account"]] = $gain["value"];
		}
	}
	arsort($starsgain);
	foreach ($starsgain as $userID => $stars) {
		if ($stars == 0 or $xi >= 100) {
			break;
		}
		$query = $db->prepare("SELECT * FROM users WHERE userID = :userID");
		$query->execute([':userID' => $userID]);
		$user = $query->fetchAll()[0];
		$xi++;
		$lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":4:-1:13:-1:17:" . $user["userCoins"] . ":6:" . $xi . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":14:" . $user["iconType"] . ":15:" . $user["special"] . ":16:" . $extid . ":3:" . $stars . ":7:" . $user["extID"] . "|";
	}  
}
if ($lbstring == "") {
	exit("-1");
}
$lbstring = substr($lbstring, 0, -1);
echo $lbstring;
?>
