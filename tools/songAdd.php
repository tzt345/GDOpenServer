<?php
error_reporting(0);
include dirname(__FILE__)."/../incl/lib/connection.php";
require dirname(__FILE__)."/../incl/lib/generatePass.php";
$gp = new generatePass();
require_once dirname(__FILE__)."/../incl/lib/exploitPatch.php";
$ep = new exploitPatch();
include dirname(__FILE__)."/../config/reupload.php";
if ($song_reupload == -1) {
	exit("Song reuploading to this GDPS is disabled.");
}
if(!empty($_POST["songLink"])){
	if ($song_reupload != 0) {
		if (!empty($_POST["userName"]) AND !empty($_POST["password"])){
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
			if(strpos($song, 'soundcloud.com') !== false){
				$soundcloud = true;
				$songinfo = file_get_contents("https://api.soundcloud.com/resolve.json?url=".$song."&client_id=".$api_key);
				$array = json_decode($songinfo);
				if($array->downloadable == true){
					$song = trim($array->download_url . "?client_id=".$api_key);
					$name = $ep->remove($array->title);
					$author = $array->user->username;
					$author = preg_replace("/[^A-Za-z0-9 ]/", '', $author);
					echo "Processing Soundcloud song ".htmlspecialchars($name,ENT_QUOTES)." by ".htmlspecialchars($author,ENT_QUOTES)." with the download link ".htmlspecialchars($song,ENT_QUOTES)." <br>";
				}else{
					if(!$array->id){
						exit("This song is neither downloadable, nor streamable. <a href='songAdd.php'>Try again.</a>");
					}
					$song = trim("https://api.soundcloud.com/tracks/".$array->id."/stream?client_id=".$api_key);
					$name = $ep->remove($array->title);
					$author = $array->user->username;
					$author = preg_replace("/[^A-Za-z0-9 ]/", '', $author);
					echo "This song isn't downloadable, attempting to insert it anyways...<br>";
				}
			}else{
				$song = str_replace("?dl=0", "", $song);
				$song = str_replace("?dl=1", "", $song);
				$song = trim($song);
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
			$ch = curl_init($song);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_NOBODY, TRUE);
			$data = curl_exec($ch);
			$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			curl_close($ch);
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
			if($count != 0){
				echo "This song already exists in our database.";
			}else{
				if ($reuploads < $song_reupload) {
					$query = $db->prepare("INSERT INTO songs (name, authorID, authorName, size, download, hash) VALUES (:name, '9', :author, :size, :download, :hash)");
					$query->execute([':name' => $name, ':download' => $song, ':author' => $author, ':size' => $size, ':hash' => $hash]);
					echo "Song reuploaded: <b>".$db->lastInsertId()."</b><hr>";
					if ($db->lastInsertId() > 999999) {

					}
				} else {
					if ($isSongReuploadLimitDaily == 1) {
						echo "You have reached the maximum daily amount of adding songs to this GDPS.";
					} else {
						echo "You have reached the maximum amount of adding songs to this GDPS.";
					}
				}
			}
		}else{
			echo "The download link isn't a valid URL.";
		}
	}else{
		echo "Incorrect password. <a href='songAdd.php'>Try again.</a>";
	}
}else{
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