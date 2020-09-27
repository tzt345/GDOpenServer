<?php
include "../incl/lib/connection.php";
require "../incl/lib/exploitPatch.php";
require "../incl/lib/generatePass.php";
$ep = new exploitPatch();
$generatePass = new generatePass();

if (!empty($_POST["songname"]) AND !empty($_POST["songid"]) AND !empty($_POST["p"]) AND !empty($_POST["us"])){
	$song = $ep->remove($_POST["songname"]);
	$songi = $ep->remove($_POST["songid"]);
	$psw = $ep->remove($_POST["p"]);
	$us = $ep->remove($_POST["us"]);
	$pass = $generatePass->isValidUsrname($us, $psw);

	$q1 = $db->prepare("SELECT `accountID` FROM `accounts` WHERE `userName` = :us");
	$q1->execute([':us' => $us]);			
	$username = $q1->fetch()[0];
	
	$q = $db->prepare("SELECT roleID FROM roleassign WHERE accountID = :ai");
	$q->execute([':ai' => $username]);
	$result = $q->fetch()[0];

	if ($pass) {
		if ($result >= "2") {
			$query = $db->prepare("UPDATE `songs` SET `name` = :name WHERE `songs`.`ID` = :id");
			$query->execute([':name' => $song, ':id' => $songi]);
			$affected = $query->rowCount();
			if ($affected)
			{
				echo "Song edited! <b>".$song."</b> for ".$songi."";
			} else {
				echo "Song couldn't be edited!";
			}
		} else {
			echo "Not a moderator.";
		}
	} else {
		echo "Wrong password!";
	}
} else {
	echo '<form action="songChange.php" method="post">
	New Title: <input type="text" name="songname"><br>
	SongID: <input type="text" name="songid"><br>
	Username: <input type="text" name="us"><br>
	Password: <input type="password" name="p"><br>
	<input type="submit" value="Change Song">
	</form>';
}

?>