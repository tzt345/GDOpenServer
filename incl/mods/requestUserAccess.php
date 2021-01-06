<?php
chdir(__DIR__);
require_once "../lib/GJPCheck.php";
$GJPCheck = new GJPCheck();
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gjp = $ep->remove($_POST["gjp"]);
$accountID = $ep->remove($_POST["accountID"]);
$gjpresult = $GJPCheck->check($gjp, $accountID);
if ($gjpresult == 1 AND $gs->getMaxValuePermission($accountID, "actionRequestMod") == 1) { // checks if they have mod
	$permState = $gs->getMaxValuePermission($accountID, "modBadgeLevel"); // checks mod badge level so it knows what to show					   
	if ($permState >= 2) { // if the mod badge level is higher than 2, it will still show elder mod message
		echo "2";
	} else {
		echo "1";
	} 
} else {
	echo "-1";
}
?>
