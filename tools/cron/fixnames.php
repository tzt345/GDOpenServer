<?php
$date = date("d-m");
chdir(dirname(__FILE__));
echo "Please wait...<br>";
ob_flush();
flush();
set_time_limit(0);
include "../../incl/lib/connection.php";
$query = $db->prepare("SELECT userName, accountID FROM accounts");
$query->execute();
$result = $query->fetchAll();
//getting users
foreach($result as $account){
	$accountID = $account["accountID"];
	$userName = $account["userName"];
	$query4 = $db->prepare("UPDATE users SET userName = :userName WHERE extID = :accountID");
	$query4->execute([':userName' => $userName, ':accountID' => $accountID]);
	echo htmlspecialchars($accountID, ENT_QUOTES) . " - " . htmlspecialchars($userName, ENT_QUOTES) . "<br>";
	ob_flush();
	flush();
}
echo "Done<hr>";
?>
