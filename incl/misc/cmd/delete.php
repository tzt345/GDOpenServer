<?php
include dirname(__FILE__)."/../../lib/connection.php";
$query = $db->prepare("DELETE FROM levels WHERE levelID=:levelID LIMIT 1");
$query->execute([':levelID' => $levelID]);
$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES (6, 1, :levelID, :timestamp, :id)");
$query->execute([':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
if(file_exists(dirname(__FILE__)."/../../data/levels/$levelID")){
	rename(dirname(__FILE__)."/../../data/levels/$levelID", dirname(__FILE__)."/../../data/levels/deleted/$levelID");
}
exit("temp_0_Level successfully deleted.");
?>