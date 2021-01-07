<?php
//header
chdir(__DIR__);
require "../lib/connection.php";
require "../../config/levels.php";
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
require_once "../lib/generateHash.php";
$hash = new generateHash();

//initializing variables
$lvlstring = "";
$userstring = "";
$songsstring = "";
$lvlsmultistring = "";
$str = "";
$order = "uploadDate";
$orderenabled = true;
$params = array("NOT unlisted = 1");

$gameVersion = 1;
if (!empty($_POST["gameVersion"])) {
	$gameVersion = $ep->number($_POST["gameVersion"]);
}
if ($gameVersion >= 20) {
	$binaryVersion = $ep->number($_POST["binaryVersion"]);
	if ($binaryVersion > 27) {
		$gameVersion++;
	}
}
if (isset($_POST["type"])) {
	$type = $ep->number($_POST["type"]);
} else {
	$type = 0;
}
if (isset($_POST["diff"])) {
	$diff = $ep->numbercolon($_POST["diff"]);
} else {
	$diff = "-";
}

//ADDITIONAL PARAMETERS
if ($gameVersion == 0) {
	$params[] = "gameVersion <= 18";
	$gameVersion = 18;
} else {
	$params[] = "gameVersion <= '$gameVersion'";
}
if (isset($_POST["featured"]) AND $_POST["featured"] == 1) {
	$params[] = "starFeatured = 1";
}
if (isset($_POST["original"]) AND $_POST["original"] == 1) {
	$params[] = "original = 0";
}
if (isset($_POST["coins"]) AND $_POST["coins"] == 1) {
	$params[] = "starCoins = 1 AND NOT coins = 0";
}
if (isset($_POST["epic"]) AND $_POST["epic"] == 1) {
	$params[] = "starEpic = 1";
}
if (isset($_POST["uncompleted"]) AND $_POST["uncompleted"] == 1) {
	$completedLevels = $ep->numbercolon($_POST["completedLevels"]);
	$params[] = "NOT levelID IN ($completedLevels)";
}
if (isset($_POST["onlyCompleted"]) AND $_POST["onlyCompleted"] == 1) {
	$completedLevels = $ep->numbercolon($_POST["completedLevels"]);
	$params[] = "levelID IN ($completedLevels)";
}
if (isset($_POST["song"]) AND $_POST["song"] > 0) {
	if (empty($_POST["customSong"])) {
		$song = $ep->number($_POST["song"]);
		$song = $song - 1;
		$params[] = "audioTrack = '$song' AND songID = 0";
	} else {
		$song = $ep->number($_POST["song"]);
		$params[] = "songID = '$song'";
	}
}
if (isset($_POST["twoPlayer"]) AND $_POST["twoPlayer"] == 1) {
	$params[] = "twoPlayer = 1";
}
if (isset($_POST["star"]) AND $_POST["star"] > 0) {
	$params[] = "NOT starStars = 0";
}
if (isset($_POST["noStar"])) {
	$params[] = "starStars = 0";
}
if (isset($_POST["gauntlet"])) {
	$order = false;
	$gauntlet = $ep->remove($_POST["gauntlet"]);
	$query = $db->prepare("SELECT * FROM gauntlets WHERE ID = :gauntlet");
	$query->execute([':gauntlet' => $gauntlet]);
	$actualgauntlet = $query->fetch();
	$str = $actualgauntlet["level1"] . "," . $actualgauntlet["level2"] . "," . $actualgauntlet["level3"] . "," . $actualgauntlet["level4"] . "," . $actualgauntlet["level5"];
	$params[] = "levelID IN ($str)";
	$type = -1;
}
if (isset($_POST["len"])) {
	$len = $ep->numbercolon($_POST["len"]);
} else {
	$len = "-";
}
if ($len != "-" AND !empty($len)) {
	$params[] = "levelLength IN ($len)";
}

//DIFFICULTY FILTERS
switch ($diff) {
	case -1:
		$params[] = "starDifficulty = 0";
		break;
	case -3:
		$params[] = "starAuto = 1";
		break;
	case -2:
		if (!empty($_POST["demonFilter"])) {
			$demonFilter = $ep->number($_POST["demonFilter"]);
		} else {
			$demonFilter = 0;
		}
		$params[] = "starDemon = 1";
		switch ($demonFilter) {
			case 1:
				$params[] = "starDemonDiff = 3";
				break;
			case 2:
				$params[] = "starDemonDiff = 4";
				break;
			case 3:
				$params[] = "starDemonDiff = 0";
				break;
			case 4:
				$params[] = "starDemonDiff = 5";
				break;
			case 5:
				$params[] = "starDemonDiff = 6";
				break;
			default:
				break;
		}
		break;
	case "-":
		break;
	default:
		if ($diff) {
			$diff = str_replace(",", "0,", $diff) . "0";
			$params[] = "starDifficulty IN ($diff) AND starAuto = 0 AND starDemon = 0";
		}
		break;
}
//TYPE DETECTION
if (!empty($_POST["str"])) {
	$str = $ep->remove($_POST["str"]);
}
if (!empty($_POST["page"]) AND is_numeric($_POST["page"])) {
	$offset = $ep->number($_POST["page"]) . "0";
} else {
	$offset = 0;
}
if ($type == 0 OR $type == 15) { //most liked, changed to 15 in GDW for whatever reason
	$order = "likes";
	if (!empty($str) AND is_numeric($str)) {
		$params = array("levelID = '$str'");
	} else {
		$params[] = "levelName LIKE '%$str%'";
	}
}
if ($type == 1) {
	$order = "downloads";
}
if ($type == 2) {
	$order = "likes";
}
if ($type == 3) { //TRENDING
	$uploadDate = time() - 604800;
	$params[] = "uploadDate > $uploadDate ";
	$order = "likes";
}
if ($type == 5) {
	$params[] = "userID = '$str'";
}
if ($type == 6 OR $type == 17) { // Featured
	$params[] = "NOT starFeatured = 0";
	$order = "rateDate DESC, uploadDate";
}
if ($type == 16) { // Hall of Fame
	if ($epicInHall == 1) {
		$params[] = "NOT starEpic = 0";
	} else {
		$params[] = "NOT starHall = 0";
	}
	$order = "rateDate DESC, uploadDate";
}
if ($type == 7) { // Magic
	if ($isMagicSectionManual == 1) {
		$params[] = "NOT starMagic = 0";
	} else {
		$params[] = "objects > 4999";
	}
}
if ($type == 10) { // Map Packs
	$order = false;
	$params[] = "levelID IN ($str)";
}
if ($type == 11) { // Awarded
	$params[] = "NOT starStars = 0";
	$order = "rateDate DESC, uploadDate";
}
if ($type == 12) { // Followed
	$followed = $ep->numbercolon($_POST["followed"]);
	$params[] = "extID IN ($followed)";
}
if ($type == 13) { // Friends
	$accountID = $ep->remove($_POST["accountID"]);
	$gjp = $ep->remove($_POST["gjp"]);
	$gjpresult = $GJPCheck->check($gjp, $accountID);
	if ($gjpresult == 1) {
		$peoplearray = $gs->getFriends($accountID);
		$whereor = implode(",", $peoplearray);
		$params[] = "extID IN ($whereor)";
	}
}

//ACTUAL QUERY EXECUTION
$querybase = "FROM levels";
if (isset($params)) {
	$querybase .= " WHERE (" . implode(" ) AND ( ", $params) . ")";
}
$query = "SELECT * $querybase ";
if ($showCreatorBannedPeoplesLevels == 0) {
	$query2 = $db->prepare("SELECT userID FROM users WHERE isCreatorBanned = 1");
	$query2->execute();
	$banResult = $query2->fetch();
	$bannedPeople = "";
	foreach ($banResult as &$bannedPerson) {
		$bannedPeople .= $bannedPerson["roleID"] . ",";
	}
	if (!empty($bannedPeople)) {
		$bannedPeople = substr($bannedPeople, 0, -1);
		$query .= "AND ( userID NOT IN ($bannedPeople) ) ";
	}
}
if ($order) {
	$query .= "ORDER BY $order DESC ";
}
$query .= "LIMIT 10 OFFSET $offset";
$countquery = "SELECT count(*) $querybase";
$query = $db->prepare($query);
$query->execute();
$countquery = $db->prepare($countquery);
$countquery->execute();
$totallvlcount = $countquery->fetchColumn();
$result = $query->fetchAll();
foreach ($result as &$level1) {
	if ($level1["levelID"] != "") {
		$lvlsmultistring .= $level1["levelID"] . ",";
		if (!empty($gauntlet)) {
			$lvlstring .= "44:$gauntlet:";
		}
		$lvlstring .= "1:" . $level1["levelID"] . ":2:" . $level1["levelName"] . ":5:" . $level1["levelVersion"] . ":6:" . $level1["userID"] . ":8:10:9:" . $level1["starDifficulty"] . ":10:" . $level1["downloads"] . ":12:" . $level1["audioTrack"] . ":13:" . $level1["gameVersion"] . ":14:" . $level1["likes"] . ":17:" . $level1["starDemon"] . ":43:" . $level1["starDemonDiff"] . ":25:" . $level1["starAuto"] . ":18:" . $level1["starStars"] . ":19:" . $level1["starFeatured"] . ":42:" . $level1["starEpic"] . ":45:" . $level1["objects"] . ":3:" . $level1["levelDesc"] . ":15:" . $level1["levelLength"] . ":30:" . $level1["original"] . ":31:0:37:" . $level1["coins"] . ":38:" . $level1["starCoins"] . ":39:" . $level1["requestedStars"] . ":46:1:47:2:40:" . $level1["isLDM"] . ":35:" . $level1["songID"] . "|";
		if ($level1["songID"] != 0 AND $gameVersion > 18) {
			$song = $gs->getSongString($level1["songID"]);
			if ($song) {
				$songsstring .= $gs->getSongString($level1["songID"]) . "~:~";
			}
		}
		$userstring .= $gs->getUserString($level1["userID"]) . "|";
	}
}
$lvlstring = substr($lvlstring, 0, -1);
$lvlsmultistring = substr($lvlsmultistring, 0, -1);
$userstring = substr($userstring, 0, -1);
$songsstring = substr($songsstring, 0, -3);
echo $lvlstring . "#" . $userstring;
if ($gameVersion > 18) {
	echo "#" . $songsstring;
}
echo "#" . $totallvlcount . ":" . $offset . ":10#";
echo $hash->genMulti($lvlsmultistring);
?>