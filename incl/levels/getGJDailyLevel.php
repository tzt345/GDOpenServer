<?php
chdir(__DIR__);
require "../lib/connection.php";
if (empty($_POST["weekly"]) OR $_POST["weekly"] == 0) {
	$weekly = 0;
	$midnight = strtotime("tomorrow 00:00:00");
} else {
	$weekly = 1;
	$midnight = strtotime("next monday");
}
//Getting DailyID
$current = time();
$query = $db->prepare("SELECT feaID FROM dailyfeatures WHERE timestamp < :current AND type = :type ORDER BY timestamp DESC LIMIT 1");
$query->execute([':current' => $current, ':type' => $weekly]);
$dailyID = $query->fetchColumn();
//Time left
$timeleft = $midnight - $current;
if ($query->rowCount() == 0) exit("0|" . $timeleft); // too lazy to check which daily id was previously used
if ($weekly == 1) {
	$dailyID = $dailyID + 100001; //the fuck went through robtops head when he was implementing this
}
//output
echo $dailyID . "|" . $timeleft;
?>
