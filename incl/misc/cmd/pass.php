<?php
include dirname(__FILE__)."/../../lib/connection.php";
if (isset($commentarray[1]) AND is_numeric($commentarray[1])) {
	$pass = $commentarray[1];
} else {
	return false;
}
$pass = sprintf("%06d", $pass);
if($pass == "000000"){
	$pass = "0";
} else {
	$pass = "1".$pass;
}
$query = $db->prepare("UPDATE levels SET password=:password WHERE levelID=:levelID");
$query->execute([':levelID' => $levelID, ':password' => $pass]);
$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES (9, :value, :timestamp, :id, :levelID)");
$query->execute([':value' => $pass, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
exit("temp_0_Level password changed to $pass.");
?>