<?php
include dirname(__FILE__)."/../incl/lib/connection.php";
require dirname(__FILE__)."/../incl/lib/generatePass.php";
$gp = new generatePass();
require_once dirname(__FILE__)."/../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
require_once dirname(__FILE__)."/../incl/lib/mainLib.php";
$gs = new mainLib();
include dirname(__FILE__)."/../config/reupload.php";
if ($song_reupload == -1) {
	exit("Song reuploading to this GDPS is disabled.");
}
if (isset($_POST["songLink"])) {
	if ($song_reupload != 0) {
		if (isset($_POST["userName"]) AND isset($_POST["password"])){
			$userName = $ep->remove($_POST["userName"]);
			$password = $ep->remove($_POST["password"]);
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName=:userName");	
			$query->execute([':userName' => $userName]);
			$accountID = $query->fetchColumn();
			if ($query->rowCount() == 0) {
				exit("Invalid or non-existant account. <a href='songAdd.php'>Try again.</a>");
			}
			$pass = $gp->isValidUsrname($userName, $password);
		} else {
			exit('<form action="songAdd.php" method="post">
				Username: <input type="text" name="userName">
				<br>Password: <input type="password" name="password">
				<br>Link: <input type="text" name="songLink">
				<br><input type="submit" value="Add Song">
				</form>');
		}
	} else {
		$pass = 1;
	}
	if ($pass == 1) {
		$song = str_replace("www.dropbox.com","dl.dropboxusercontent.com", $_POST["songLink"]);
		if (filter_var($song, FILTER_VALIDATE_URL) == true) {
			$soundcloud = false;
			if (strpos($song, 'soundcloud.com') !== false) {
				$soundcloud = true;
				$songinfo = file_get_contents("https://api.soundcloud.com/resolve.json?url=".$song."&client_id=".$api_key);
				$array = json_decode($songinfo);
				if ($array->downloadable == true) {
					$song = trim($array->download_url . "?client_id=".$api_key);
					$name = $ep->remove($array->title);
					$author = $array->user->username;
					$author = preg_replace("/[^A-Za-z0-9 ]/", '', $author);
					echo "Processing Soundcloud song ".htmlspecialchars($name, ENT_QUOTES)." by ".htmlspecialchars($author, ENT_QUOTES)." with the download link ".htmlspecialchars($song, ENT_QUOTES)." <br>";
				} else {
					if (!$array->id) {
						exit("This song is neither downloadable, nor streamable. <a href='songAdd.php'>Try again.</a>");
					}
					$song = trim("https://api.soundcloud.com/tracks/".$array->id."/stream?client_id=".$api_key);
					$name = $ep->remove($array->title);
					$author = $array->user->username;
					$author = preg_replace("/[^A-Za-z0-9 ]/", '', $author);
					echo "This song isn't downloadable, attempting to insert it anyways...<br>";
				}
			} else {
				$song = str_replace("?dl=0", "", $song);
				$song = str_replace("?dl=1", "", $song);
				$song = trim($song);
				$song = urlencode($song);
				$name = str_replace(".mp3", "", basename($song));
				$name = str_replace(".webm", "", $name);
				$name = str_replace(".mp4", "", $name);
				$name = urldecode($name);
				$name = $ep->remove($name);
				$author = "Reupload";
			}
			if (!$soundcloud AND $canReuploadFromDirectLinks == 0) {
				exit("Reuploading from direct links is disabled in this GDPS. <a href='songAdd.php'>Try again.</a>");
			}
			$size = $gs->getFileSize($song);
			$size = round($size / 1024 / 1024, 2);
			$hash = "";
			//$hash = sha1_file($song);
			$count = 0;
			$query = $db->prepare("SELECT count(*) FROM songs WHERE download = :download");
			$query->execute([':download' => $song]);	
			$count = $query->fetchColumn();
			/* if(!$soundcloud){
				//$query = $db->prepare("SELECT count(*) FROM songs WHERE hash = :hash");
				//$query->execute([':hash' => $hash]);
				//$count += $query->fetchColumn();
			} */
			if ($count != 0) {
				echo "This song already exists in our database.";
			} else {
				if ($song_reupload != 0) {
					//checking the amount of reuploads
					if($isSongReuploadLimitDaily == 1) {
						$query = $db->prepare("SELECT value2 FROM actions WHERE type = 18 AND value = :accountID AND timestamp > :timestamp");
						$query->execute([':accountID' => $accountID, ':timestamp' => time() - 86400]);
					} else {
						$query = $db->prepare("SELECT value2 FROM actions WHERE type = 18 AND value = :accountID");
						$query->execute([':accountID' => $accountID]);
					}
					
					if($query->rowCount() == 0) {
						$query = $db->prepare("INSERT INTO actions (type, value, value2, timestamp) VALUES (18, :accountID, 1, :timestamp)");
						$query->execute([':accountID' => $accountID, ':timestamp' => time()]);
						$reuploads = 1;
					} else {
						$reuploads = $query->fetchColumn();
						if($isSongReuploadLimitDaily == 1) {
							$query = $db->prepare("UPDATE actions SET value2 = ".($reuploads + 1)." WHERE type = 18 AND value = :accountID AND timestamp > :timestamp");
							$query->execute([':accountID' => $accountID, ':timestamp' => time() - 86400]);
						} else {
							$query = $db->prepare("UPDATE actions SET value2 = ".($reuploads + 1)." WHERE type = 18 AND value = :accountID");
							$query->execute([':accountID' => $accountID]);
						}
					}
				} else {
					$reuploads = -1;
				}
				if ($reuploads < $song_reupload) {
					$query = $db->prepare("INSERT INTO songs (name, authorID, authorName, size, download, hash) VALUES (:name, '9', :author, :size, :download, :hash)");
					$query->execute([':name' => $name, ':download' => $song, ':author' => $author, ':size' => $size, ':hash' => $hash]);
					echo "Song reuploaded: <b>".$db->lastInsertId()."</b><hr><br>If the ID isn't correct, try adding one to the ID ";
					if ($db->lastInsertId() > 999999) {
						require_once dirname(__FILE__)."/../incl/lib/mainLib.php";
						$gs = new mainLib();
						$queryd = $db->prepare("INSERT INTO levels (levelName, gameVersion, binaryVersion, userName, levelDesc, levelVersion, levelLength, audioTrack, auto, password, original, twoPlayer, songID, objects, coins, requestedStars, extraString, levelString, levelInfo, uploadDate, userID, extID, updateDate, unlisted, hostname, isLDM) VALUES (:levelName, 19, 19, :userName, 'QXV0by1HZW5lcmF0ZWQgU29uZyBMZXZlbA==', 1, 0, 0, 0, 0, 0, 0, :songID, 1, 0, 0, '29_29_29_40_29_29_29_29_29_29_29_29_29_29_29_29', '', 0, :uploadDate, :userID, :id, :uploadDate, 1, '127.0.0.1', 0)");
						$queryd->execute([':levelName' => "Song ID ".$db->lastInsertId(), ':userName' => $gs->getAccountName($botAID), ':songID' => $db->lastInsertId(), ':uploadDate' => time(), ':userID' => $botUID, ':id' => $botAID]);
						$levelID = $db->lastInsertId();
						file_put_contents("../data/levels/$levelID", "H4sIAAAAAAAAC6WQ0Q3CMAxEFwqSz4nbVHx1hg5wA3QFhgfn4K8VRfzci-34Kcq-1V7AZnTCg5UeQUBwQc3GGzgRZsaZICKj09iJBzgU5tcU-F-xHCryjhYuSZy5fyTK3_iI7JsmTjX2y2umE03ZV9RiiRAmoZVX6jyr80ZPbHUZlY-UYAzWNlJTmIBi9yfXQXYGDwIAAA==");
						echo "<br>Go to level ID $levelID to download the song.";
					}
				} else {
					if ($isSongReuploadLimitDaily == 1) {
						echo "You have reached the maximum daily amount of adding songs to this GDPS.";
					} else {
						echo "You have reached the maximum amount of adding songs to this GDPS.";
					}
				}
			}
		} else {
			echo "The download link isn't a valid URL.";
		}
	} else {
		echo "Incorrect password. <a href='songAdd.php'>Try again.</a>";
	}
} else {
	if ($song_reupload != 0) {
		echo '<form action="songAdd.php" method="post">
		Username: <input type="text" name="userName">
		<br>Password: <input type="password" name="password">
		<br>Link: <input type="text" name="songLink">
		<br><input type="submit" value="Add Song">
		</form>';
	} else {
		echo '<form action="songAdd.php" method="post">
		Link: <input type="text" name="songLink">
		<br><input type="submit" value="Add Song">
		</form>';
	}
}
?>