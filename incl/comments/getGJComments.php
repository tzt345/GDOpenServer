<?php
chdir(__DIR__);
//error_reporting(0);
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$gameVersion = $ep->remove($_POST["gameVersion"]);
if ($gameVersion <= 19) {
	$binaryVersion = 31;
} else {
	$binaryVersion = $ep->remove($_POST["binaryVersion"]);
}
$commentstring = "";
$userstring = "";
$users = array();
if(isset($_POST["mode"])){
	$mode = $ep->remove($_POST["mode"]);
}else{
	$mode = 0;
}
if(isset($_POST["count"]) AND is_numeric($_POST["count"])){
	$count = $ep->remove($_POST["count"]);
}else{
	$count = 10;
}
$page = $ep->remove($_POST["page"]);
$commentpage = $page * $count;
if($mode == 0){
	$modeColumn = "commentID";
}else{
	$modeColumn = "likes";
}
if(empty($_POST["levelID"]) OR !$_POST["levelID"]){
	$displayLevelID = true;
	$levelID = $ep->remove($_POST["userID"]);
	$query = "SELECT levelID, commentID, timestamp, comment, userID, likes, isSpam, percent FROM comments WHERE userID = :levelID ORDER BY $modeColumn DESC LIMIT $count OFFSET $commentpage";
	$countquery = "SELECT count(*) FROM comments WHERE userID = :levelID";
}else{
	$displayLevelID = false;
	$levelID = $ep->remove($_POST["levelID"]);
	$query = "SELECT levelID, commentID, timestamp, comment, userID, likes, isSpam, percent FROM comments WHERE levelID = :levelID ORDER BY $modeColumn DESC LIMIT $count OFFSET $commentpage";
	$countquery = "SELECT count(*) FROM comments WHERE levelID = :levelID";
}
$countquery = $db->prepare($countquery);
$countquery->execute([':levelID' => $levelID]);
$commentcount = $countquery->fetchColumn();
if($commentcount == 0){
	exit("-2");
}
$query = $db->prepare($query);
$query->execute([':levelID' => $levelID]);
$result = $query->fetchAll();
function timing ($time) {
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array (31536000 => 'year', 2592000 => 'month', 604800 => 'week', 86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second');
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits > 1) ? 's' : '');
    }
}
foreach($result as &$comment1) {
	if($comment1["commentID"] != ""){
		$uploadDate = timing($comment1["timestamp"]);
		$actualcomment = $comment1["comment"];
		if($gameVersion < 20){
			$actualcomment = base64_decode($actualcomment);
		}
		if($displayLevelID){
			$commentstring .= "1~".$comment1["levelID"]."~";
		}
		$commentstring .= "2~".$actualcomment."~3~".$comment1["userID"]."~4~".$comment1["likes"]."~5~0~7~".$comment1["isSpam"]."~9~".$uploadDate."~6~".$comment1["commentID"]."~10~".$comment1["percent"];
		$query12 = $db->prepare("SELECT userID, userName, icon, color1, color2, iconType, special, extID FROM users WHERE userID = :userID");
		$query12->execute([':userID' => $comment1["userID"]]);
		if ($query12->rowCount() > 0) {
			$user = $query12->fetchAll()[0];
			if(is_numeric($user["extID"])){
				$extID = $user["extID"];
			}else{
				$extID = 0;
			}
			if(!in_array($user["userID"], $users)){
				$users[] = $user["userID"];
				$userstring .=  $user["userID"] . ":" . $user["userName"] . ":" . $extID . "|";
			}
			if($binaryVersion > 31){
				$colorado = $gs->getAccountCommentColor($extID);
				if ($colorado == "random") {
					$colorado = rand(0,255).",".rand(0,255).",".rand(0,255);
				}
				$commentstring .= "~11~".$gs->getMaxValuePermission($extID, "modBadgeLevel")."~12~".$colorado.":1~".$user["userName"]."~7~1~9~".$user["icon"]."~10~".$user["color1"]."~11~".$user["color2"]."~14~".$user["iconType"]."~15~".$user["special"]."~16~".$user["extID"];
			}
			$commentstring .= "|";
		}
	}
}
$commentstring = substr($commentstring, 0, -1);
$userstring = substr($userstring, 0, -1);
echo $commentstring;
if($binaryVersion < 32){
	echo "#$userstring";
}
echo "#".$commentcount.":".$commentpage.":10";
?>