<?php
function noshare($uploadDate, $accountID, $levelID) {
	include dirname(__FILE__)."/../../lib/connection.php";
	$query = $db->prepare("DELETE FROM cpshares WHERE levelID=:levelID");
	$query->execute([':levelID' => $levelID]);
	return true;
}
?>