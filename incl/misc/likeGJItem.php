<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$type = $_POST["type"] + 2;
$ip = $gs->getIP();
$itemID = $ep->remove($_POST["itemID"]);
$query6 = $db->prepare("SELECT count(*) FROM actions WHERE type = :type AND value = :itemID AND value2 = :ip");
$query6->execute([':type' => $type, ':itemID' => $itemID, ':ip' => $ip]);
if ($query6->fetchColumn() > 2) {
	exit("-1");
}
$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES (:type, :itemID, :time, :ip)");
$query6->execute([':type' => $type, ':itemID' => $itemID, ':time' => time(), ':ip' => $ip]);
switch ($_POST["type"]) {
	case 1:
		$table = "levels";
		$column = "levelID";
		break;
	case 2:
		$table = "comments";
		$column = "commentID";
		break;
	case 3:
		$table = "acccomments";
		$column = "commentID";
		break;
}
if ($_POST["like"] == 1) {
	$likes = "1";
} else {
	$likes = "-1";
}
$query2 = $db->prepare("UPDATE $table SET likes = likes + :likes WHERE $column = :itemID");
$query2->execute([':itemID' => $itemID, ':likes' => $likes]);
echo "1";
?>