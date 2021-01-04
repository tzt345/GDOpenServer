<?php
if (isset($commentArray[3])) {
	$timeArg = $commentArray[3];
	$timeSuffix = substr($timeArg, -1);
	switch ($timeSuffix) {
		case "m":
			try {
				$time = int(trim($timeArg, "m")) * 60;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		case "h":
			try {
				$time = int(trim($timeArg, "h")) * 3600;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		case "d":
			try {
				$time = int(trim($timeArg, "d")) * 86400;
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
		default:
			try {
				$time = $uploadDate + int($timeArg);
			} catch (Exception $e) {
				exit("temp_0_Error: Invalid input for argument 'Time'.");
			}
			break;
	}
	if ($time > 0) {
		$timeAgo = $uploadDate - $time;
	} elseif ($time < 0) {
		exit("temp_0_Error: Invalid input for argument 'Time'.");
	}
} else {
	exit("temp_0_Error: No input given for required argument 'Time Ago'.");
}
$query = $db->prepare("SELECT count(*) FROM actions WHERE value = :levelID AND type = 3 AND timestamp >= :timestamp");
$query->execute([':levelID' => $levelID, ':timestamp' => $timeAgo]);

if ($query->rowCount() == 0) {
    exit("temp_0_Error: No likes were found by the given timestamp.");
}
$count = $query->fetchColumn();
$query2 = $db->prepare("UPDATE levels SET likes = likes + :count WHERE levelID = :levelID");
$query2->execute([':levelID' => $levelID, ':count' => $count]);
$query3 = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES (19, :levelID, 1, :now, :account)");
$query3->execute([':levelID' => $levelID, ':timestamp' => $timestamp, ':now' => $uploadDate, ':account' => $accountID]);
exit("temp_0_$count likes from this level have been reverted.");
?>