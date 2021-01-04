<?php
if (isset($commentArray[1]) AND is_numeric($commentArray[1])) {
	$pass = $commentArray[1];
	$pass = sprintf("%06d", $pass);
	if ($pass == "disable") {
		$pass = "0";
	} else {
		if ($pass == "000000") {
			$pass = "1";
		} else {
			$pass = "1" . $pass;
		}
	}
} else {
	exit("temp_0_Error: No input given for required argument 'Password'.");
}
$query = $db->prepare("UPDATE levels SET password = :password WHERE levelID = :levelID");
$query->execute([':levelID' => $levelID, ':password' => $pass]);
$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES (9, :value, :timestamp, :id, :levelID)");
$query->execute([':value' => $pass, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
if ($pass == "0") {
	exit("temp_0_Level copying has been disabled.");
} elseif ($pass == "1") {
	exit("temp_0_Level copy password has been removed.");
} else {
	exit("temp_0_Level copy password has been changed to $pass.");
}
?>