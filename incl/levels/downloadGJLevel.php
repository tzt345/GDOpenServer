<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
require_once "../lib/generateHash.php";
$hash = new generateHash();
$gameVersion = 1;
if (!empty($_POST["gameVersion"])) {
	$gameVersion = $ep->remove($_POST["gameVersion"]);
}
if (empty($_POST["levelID"]) AND !is_numeric($_POST["levelID"])) {
	exit("-1");
} else {
	$levelID = $ep->remove($_POST["levelID"]);
}
$ip = $gs->getIP();
$feaID = 0;
$now = time();
switch ($levelID) {
	case -1:
		$query = $db->prepare("SELECT feaID, levelID FROM dailyfeatures WHERE timestamp < :time AND type = 0 ORDER BY timestamp DESC LIMIT 1");
		$query->execute([':time' => $now]);
		$result = $query->fetch();
		// todo: find proper endpoint result for non-ready daily/weekly
		if ($query->rowCount() == 0) exit(-1);
		$levelID = $result["levelID"];
		$feaID = $result["feaID"];
		$daily = 1;
		break;
	case -2:
		$query = $db->prepare("SELECT feaID, levelID FROM dailyfeatures WHERE timestamp < :time AND type = 1 ORDER BY timestamp DESC LIMIT 1");
		$query->execute([':time' => $now]);
		$result = $query->fetch();
		if ($query->rowCount() == 0) exit(-1);
		$levelID = $result["levelID"];
		$feaID = $result["feaID"];
		$feaID = $feaID + 100001;
		$daily = 1;
		break;
	default:
		$daily = 0;
}
//downloading the level
$query = $db->prepare("SELECT * FROM levels WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID]);
$lvls = $query->rowCount();
if ($lvls != 0) {
	$result = $query->fetch();
	//adding the download
	$query6 = $db->prepare("SELECT count(*) FROM actions WHERE type = 7 AND value = :itemID AND value2 = :ip");
	$query6->execute([':itemID' => $levelID, ':ip' => $ip]);
	if ($query6->fetchColumn() < 2) {
		$query2 = $db->prepare("UPDATE levels SET downloads = downloads + 1 WHERE levelID = :levelID");
		$query2->execute([':levelID' => $levelID]);
		$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (7, :itemID, :time, :ip)");
		$query6->execute([':itemID' => $levelID, ':time' => $now, ':ip' => $ip]);
	}
	$uploadDate = $gs->makeTime($result["uploadDate"]);
	$updateDate = $gs->makeTime($result["updateDate"]);
	//password xor
	$xorPass = $result["password"];
	$desc = $result["levelDesc"];
	if ($gs->checkModIPPermission("actionFreeCopy") == 1) {
		$xorPass = "1";
	}
	if ($gameVersion > 19) {
		if ($xorPass != 0) {
			require "../lib/XORCipher.php";
			$xor = new XORCipher();
			$xorPass = base64_encode($xor->cipher($xorPass, 26364));
		}
	} else {
		$desc = $ep->remove(base64_decode($desc));
	}
	//submitting data
	if (file_exists("../../data/levels/$levelID")) {
		$levelstring = file_get_contents("../../data/levels/$levelID");
	} else {
		$levelstring = $result["levelString"];
	}
	if ($gameVersion > 18 AND substr($levelstring, 0, 3) == 'kS1') {
		$levelstring = base64_encode(gzcompress($levelstring));
		$levelstring = str_replace("/", "_", $levelstring);
		$levelstring = str_replace("+", "-", $levelstring);
	}
	if ($result["starDifficulty"] != 0) {
		$diffDenominator = "10";
	} else {
		$diffDenominator = "0";
	}
	$response = "1:" . $result["levelID"] . ":2:" . $result["levelName"] . ":3:" . $desc . ":4:" . $levelstring . ":5:" . $result["levelVersion"] . ":6:" . $result["userID"] . ":8:" . $diffDenominator . ":9:" . $result["starDifficulty"] . ":10:" . $result["downloads"] . ":11:1:12:" . $result["audioTrack"] . ":13:" . $result["gameVersion"] . ":14:" . $result["likes"] . ":17:" . $result["starDemon"] . ":43:" . $result["starDemonDiff"] . ":25:" . $result["starAuto"] . ":18:" . $result["starStars"] . ":19:" . $result["starFeatured"] . ":42:" . $result["starEpic"] . ":45:" . $result["objects"] . ":15:" . $result["levelLength"] . ":30:" . $result["original"] . ":31:" . $result["twoPlayer"] . ":28:" . $uploadDate . ":29:" . $updateDate . ":35:" . $result["songID"] . ":36:" . $result["extraString"] . ":37:" . $result["coins"] . ":38:" . $result["starCoins"] . ":39:" . $result["requestedStars"] . ":46:1:47:2:48:1:40:" . $result["isLDM"] . ":27:$xorPass";
	if ($daily == 1) {
		$response .= ":41:" . $feaID;
	}
	//2.02 stuff
	$response .= "#" . $hash->genSolo($levelstring) . "#";
	//2.1 stuff
	$somestring = $result["userID"] . "," . $result["starStars"] . "," . $result["starDemon"] . "," . $result["levelID"] . "," . $result["starCoins"] . "," . $result["starFeatured"] . "," . $xorPass . "," . $feaID;
	$response .= $hash->genSolo2($somestring) . "#";
	if ($daily == 1) {
		$extID = $gs->getExtID($result["userID"]);
		if (!is_numeric($extID)) {
			$extID = 0;
		}
		$response .= $gs->getUserString($result["userID"]);
	} else {
		$response .= $somestring;
	}
	echo $response;
} else {
	echo "-1";
}
?>