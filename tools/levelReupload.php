<html>
<head>
<title>LEVEL REUPLOAD</title>
</head>
<body>
<?php
function chkarray($source) {
	if ($source == "") {
		$target = "0";
	} else {
		$target = $source;
	}
	return $target;
}
require "../incl/lib/connection.php";
require "../config/users.php";
require "../config/reupload.php";
require_once "../incl/lib/XORCipher.php";
$xc = new XORCipher();
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
if ($levelReupload <= -1) {
	exit("Level reuploading to this GDPS is disabled.");
}
if (!empty($_POST["levelID"])) {
	$levelID = $_POST["levelID"];
	$levelID = preg_replace("/[^0-9]/", '', $levelID);
	$url = $_POST["server"];
	$post = ['gameVersion' => '21', 'binaryVersion' => '33', 'gdw' => '0', 'levelID' => $levelID, 'secret' => 'Wmfd2893gb7', 'inc' => '1', 'extras' => '0'];
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$result = curl_exec($ch);
	curl_close($ch);
	if ($result == "" OR $result == "-1" OR $result == "No no no") {
		if ($result == "") {
			echo "An error has occured while connecting to the server.";
		} elseif ($result == "-1") {
			echo "This level doesn't exist.";
		} else {
			echo "RobTop doesn't like you or something....";
		}
		echo "<br>Error code: $result. <a href='levelReupload.php'>Try again.</a>";
	} else {
		$level = explode('#', $result)[0];
		$resultarray = explode(':', $level);
		$levelarray = array();
		$x = 1;
		foreach ($resultarray as &$value) {
			if ($x % 2 == 0) {
				$levelarray["a$arname"] = $value;
			} else {
				$arname = $value;
			}
			$x++;
		}
		if ($_POST["debug"] == 1) {
			echo "<br>" . $result . "<br>";
			var_dump($levelarray);
		}
		if ($levelarray["a4"] == "") {
			echo "An error has occured.<br>
			Error code: " . htmlspecialchars($result, ENT_QUOTES) . "";
		}
		$uploadDate = time();
		//old levelString
		$levelString = chkarray($levelarray["a4"]);
		$gameVersion = chkarray($levelarray["a13"]);
		if (substr($levelString, 0, 2) == 'eJ'){
			$levelString = str_replace("_", "/", $levelString);
			$levelString = str_replace("-", "+", $levelString);
			$levelString = gzuncompress(base64_decode($levelString));
			if ($gameVersion > 18) {
				$gameVersion = 18;
			}
		}
		//check if exists
		$query = $db->prepare("SELECT levelID FROM levels WHERE originalReup = :lvl OR original = :lvl");
		$query->execute([':lvl' => $levelarray["a1"]]);
		if (empty($query->fetchColumn())) {
			$parsedurl = parse_url($url);
			if ($parsedurl["host"] == $_SERVER['SERVER_NAME']) {
				exit("You're attempting to reupload from the target server.");
			}
			$hostname = $gs->getIP();
			//values
			$twoPlayer = chkarray($levelarray["a31"]);
			$songID = chkarray($levelarray["a35"]);
			$coins = chkarray($levelarray["a37"]);
			$reqstar = chkarray($levelarray["a39"]);
			$extraString = chkarray($levelarray["a36"]);
			$starStars = chkarray($levelarray["a18"]);
			$isLDM = chkarray($levelarray["a40"]);
			$password = chkarray($xc->cipher(base64_decode($levelarray["a27"]), 26364));
			if ($parsedurl["host"] == "www.boomlings.com") {
				if ($starStars != 0) {
					$starCoins = chkarray($levelarray["a38"]);
					$starDiff = chkarray($levelarray["a9"]);
					$starDemon = chkarray($levelarray["a17"]);
					$starAuto = chkarray($levelarray["a25"]);
				}
			} else {
				$starStars = 0;
				$starCoins = 0;
				$starDiff = 0;
				$starDemon = 0;
				$starAuto = 0;
			}
			$targetUserID = chkarray($levelarray["a6"]);
			//linkacc
			$query = $db->prepare("SELECT accountID, userID FROM links WHERE targetUserID = :target AND server = :url");
			$query->execute([':target' => $targetUserID, ':url' => $parsedurl["host"]]);
			if ($query->rowCount() == 0) {
				if ($levelReupload == 0) {
					$userID = $botUID;
					$extID = $botAID;
				} else {
					exit("Please link your account at <a href='linkAccount.php'>here</a> and to the same server you gave(if you didn\'t change the URL box just link your account) before reuploading.");
				}
			} else {
				$userInfo = $query->fetchAll()[0];
				$userID = $userInfo["userID"];
				$extID = $userInfo["accountID"];
			}
			if ($levelReupload > 0) {
				//checking the amount of reuploads
				if ($isLevelReuploadLimitDaily == 1) {
					$dailyTime = strtotime("-1 days", strtotime("12:00:00"))
					$query = $db->prepare("SELECT value2 FROM actions WHERE type = 17 AND value = :accountID AND timestamp > :timestamp");
					$query->execute([':accountID' => $extID, ':timestamp' => $dailyTime]);
				} else {
					$query = $db->prepare("SELECT value2 FROM actions WHERE type = 17 AND value = :accountID");
					$query->execute([':accountID' => $extID]);
				}
				
				if ($query->rowCount() == 0) {
					$query = $db->prepare("INSERT INTO actions (type, value, value2, timestamp) VALUES (17, :accountID, 1, :timestamp)");
					$query->execute([':accountID' => $extID, ':timestamp' => $uploadDate]);
					$reuploads = 1;
				} else {
					$reuploads = $query->fetchColumn();
					if ($isLevelReuploadLimitDaily == 1) {
						$query = $db->prepare("UPDATE actions SET value2 = " . ($reuploads + 1) . " WHERE type = 17 AND value = :accountID AND timestamp > :timestamp");
						$query->execute([':accountID' => $extID, ':timestamp' => $dailyTime]);
					} else {
						$query = $db->prepare("UPDATE actions SET value2 = " . ($reuploads + 1) . " WHERE type = 17 AND value = :accountID");
						$query->execute([':accountID' => $extID]);
					}
				}
			} else {
				$reuploads = -2;
			}
			
			if ($reuploads < $levelReupload) {
				//query
				$query = $db->prepare("INSERT INTO levels (levelName, gameVersion, binaryVersion, userName, levelDesc, levelVersion, levelLength, audioTrack, auto, password, original, twoPlayer, songID, objects, coins, requestedStars, extraString, levelString, levelInfo, uploadDate, updateDate, originalReup, userID, extID, unlisted, hostname, starStars, starCoins, starDifficulty, starDemon, starAuto, isLDM) VALUES (:name, :gameVersion, 27, 'Reupload', :desc, :version, :length, :audiotrack, 0, :password, :originalReup, :twoPlayer, :songID, 0, :coins, :reqstar, :extraString, :levelString, 0, :uploadDate, :uploadDate, :originalReup, :userID, :extID, 0, :hostname, :starStars, :starCoins, :starDifficulty, :starDemon, :starAuto, :isLDM)");
				$query->execute([':password' => $password, ':starDemon' => $starDemon, ':starAuto' => $starAuto, ':gameVersion' => $gameVersion, ':name' => $levelarray["a2"], ':desc' => $levelarray["a3"], ':version' => $levelarray["a5"], ':length' => $levelarray["a15"], ':audiotrack' => $levelarray["a12"], ':twoPlayer' => $twoPlayer, ':songID' => $songID, ':coins' => $coins, ':reqstar' => $reqstar, ':extraString' => $extraString, ':levelString' => "", ':uploadDate' => $uploadDate, ':originalReup' => $levelarray["a1"], ':hostname' => $hostname, ':starStars' => $starStars, ':starCoins' => $starCoins, ':starDifficulty' => $starDiff, ':userID' => $userID, ':extID' => $extID, ':isLDM' => $isLDM]);
				$levelID = $db->lastInsertId();
				file_put_contents("../data/levels/$levelID", $levelString);
				echo "Level reuploaded, ID: $levelID<br><hr><br>";
			} elseif ($isLevelReuploadLimitDaily == 1) {
				echo "You have reached the maximum daily amount of reuploading levels to this GDPS.";
			} else {
				echo "You have reached the maximum amount of reuploading levels to this GDPS.";
			}
		} else {
			echo "This level has been already reuploaded. The ID of the level on this server is " . $query->fetchColumn() . ".";
		}
	}
} else {
	if ($levelReupload > 0) {
		echo "Linking your account to an account you own in the target server you want to reupload from before reuploading is required due to the limitations applied here in this private server ($levelReupload ";
		if ($isLevelReuploadLimitDaily == 1) {
			echo "daily";
		} else {
			echo "permanent";
		}
		echo " reuploads for each account).<br>";
	}
	echo '<form action="levelReupload.php" method="post">
		ID: <input type="text" name="levelID"><br>
		URL (Don\'t change if you don\'t know what you are doing): <input type="text" name="server" value="http://www.boomlings.com/database/downloadGJLevel22.php"><br>
		Debug Mode (0=off, 1=on): <input type="text" name="debug" value="0"><br>
		<input type="submit" value="Reupload">
	</form><br>
	Alternative servers to reupload from:<br>
	http://www.boomlings.com/database/downloadGJLevel22.php - Robtops server<br>
	http://pi.michaelbrabec.cz:9010/a/downloadGJLevel22.php - CvoltonGDPS<br>
	http://teamhax.altervista.org/dbh/downloadGJLevel22.php - TeamHax GDPS';
}
?>
</body>
</html>
