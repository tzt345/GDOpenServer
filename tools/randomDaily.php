<?php
include "../incl/lib/connection.php";

if (!empty($_POST["count"]) AND is_numeric($_POST["count"]) AND !empty($_POST["count2"]) AND is_numeric($_POST["count2"])){
for ($x = 1; $x <= $_POST["count"]; $x++) {
	// Get Daily
	onfaildaily:
	$query = $db->prepare("SELECT levelID FROM levels WHERE starStars >= 2 OR starStars <= 8 ORDER BY RAND() LIMIT 1;");
	$query->execute();
	$dailylevel = $query->fetch()[0];
	$query = $db->prepare("SELECT count(*) FROM dailyfeatures WHERE levelID = :level AND type = 0");
	$query->execute([':level' => $dailylevel]);
	if($query->fetchColumn() != 0){
		goto onfaildaily;
	}
	// Add Daily
	$query = $db->prepare("SELECT timestamp FROM dailyfeatures WHERE timestamp >= :tomorrow AND type = 0 ORDER BY timestamp DESC LIMIT 1");
		$query->execute([':tomorrow' => strtotime("tomorrow 00:00:00")]);
	if($query->rowCount() == 0){
		$timestamp = strtotime("tomorrow 00:00:00");
	}else{
		$timestamp = $query->fetchColumn() + 86400;
	}
	$query = $db->prepare("INSERT INTO dailyfeatures (levelID, timestamp, type) VALUES (:levelID, :uploadDate, 0)");
	$query->execute([':levelID' => $dailylevel, ':uploadDate' => $timestamp]);
}
for ($x = 1; $x <= $_POST["count2"]; $x++) {
	// Get Weekly
	onfailweekly:
	$query = $db->prepare("SELECT levelID FROM levels WHERE starStars = 10 & starDemonDiff <= 2 & starDemonDiff != 0 ORDER BY RAND() LIMIT 1;");
	$query->execute();
	$weeklylevel = $query->fetch()[0];
	$query = $db->prepare("SELECT count(*) FROM dailyfeatures WHERE levelID = :level AND type = 1");
	$query->execute([':level' => $weeklylevel]);
	if($query->fetchColumn() != 0){
		goto onfailweekly;
	}
	// Add Weekly
	$query = $db->prepare("SELECT timestamp FROM dailyfeatures WHERE timestamp >= :tomorrow AND type = 1 ORDER BY timestamp DESC LIMIT 1");
		$query->execute([':tomorrow' => strtotime("next monday")]);
	if($query->rowCount() == 0){
		$timestamp = strtotime("next monday");
	}else{
		$timestamp = $query->fetchColumn() + 604800;
	}
	$query = $db->prepare("INSERT INTO dailyfeatures (levelID, timestamp, type) VALUES (:levelID, :uploadDate, 1)");
	$query->execute([':levelID' => $weeklylevel, ':uploadDate' => $timestamp]);
}
// Finish
echo "Added ".$_POST["count"]." new dailies and ".$_POST["count2"]." weeklies.";
} else {
	echo '<form action="randomDaily.php" method="post">
	Dailiy Amount: <input type="text" placeholder="7" name="count"><br>
	Weekly Amount: <input type="text" placeholder="1" name="count2"><br>
	<input type="submit" value="ROLL IT">
	</form>';
	if (!empty($_POST["count"]) AND !is_numeric($_POST["count"])){
		echo "Enter the shit as numbers goddamnit";
	}
}

?>