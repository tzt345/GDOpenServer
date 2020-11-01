<h1>TOP LEADERBOARD PROGRESS</h1>
<table border="1"><tr><th>Rank</th><th>UserID</th><th>UserName</th><th>Stars</th></tr>
<?php
//error_reporting(0);
include "../../incl/lib/connection.php";
$starsgain = array();
$time = time() - 86400;
$x = 0;
$query = $db->prepare("SELECT * FROM actions WHERE type = 9 AND timestamp > :time");
$query->execute([':time' => $time]);
$result = $query->fetchAll();
foreach($result as &$gain){
	if(isset($starsgain[$gain["account"]])){
		$starsgain[$gain["account"]] += $gain["value"];
	}else{
		$starsgain[$gain["account"]] = $gain["value"];
	}
}
arsort($starsgain);
foreach ($starsgain as $userID => $stars){
	$query = $db->prepare("SELECT userName, isLeaderboardBanned, isBanned FROM users WHERE userID = :userID LIMIT 1");
	$query->execute([':userID' => $userID]);
	$userinfo = $query->fetch();
	$username = htmlspecialchars($userinfo["userName"], ENT_QUOTES);
	if($userinfo["isLeaderboardBanned"] == 0 AND $userinfo["isBanned"] == 0){
		$x++;
		echo "<tr><td>$x</td><td>$userID</td><td>$username</td><td>$stars</td></tr>";
	}
}  
?>
</table>