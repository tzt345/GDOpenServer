<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
include "../../config/users.php";
require_once "../lib/GJPCheck.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gjp = $ep->remove($_POST["gjp"]);
$stars = $ep->remove($_POST["stars"]);
$levelID = $ep->remove($_POST["levelID"]);
$accountID = $ep->remove($_POST["accountID"]);
if($accountID != "" AND $gjp != ""){
	$GJPCheck = new GJPCheck();
	$gjpresult = $GJPCheck->check($gjp, $accountID);
	if($gjpresult == 1){
		$difficulty = $gs->getDiffFromStars($stars);
		if($gs->checkPermission($accountID, "actionRateStars")){
			$gs->rateLevel($accountID, $levelID, 0, $difficulty["diff"], $difficulty["auto"], $difficulty["demon"]);
			echo 1;
		} elseif ($gs->checkPermission($accountID, "actionSuggestRating") || $nonModsCanSuggest == 1) {
			$gs->suggestLevel($accountID, $levelID, $difficulty["diff"], $stars, $feature, $difficulty["auto"], $difficulty["demon"]);
			echo 1;
		} else {
			echo -1;
		}
	}else{
		echo -1;
	}
}else{
	echo -1;
}