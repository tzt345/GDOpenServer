<?php
include dirname(__FILE__)."/../../lib/connection.php";
$query = $db->prepare("DELETE FROM cpshares WHERE levelID=:levelID");
$query->execute([':levelID' => $levelID]);
exit("temp_0_The level is no longer sharing awarded creator points with other users.");
?>