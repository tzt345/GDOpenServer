<h1>Top Weekly Progression Leaderboard</h1>
<table border="1"><tr><th>Rank</th><th>UserID</th><th>UserName</th><th>Stars</th></tr>
<?php
require "../../incl/lib/connection.php";
$starsgain = array();
$time = time() - 86400;
$x = 0;
$query = $db->prepare("SELECT * FROM actions WHERE type = 9 AND timestamp > :time");
$query->execute([':time' => $time]);
$result = $query->fetchAll();
foreach ($result as &$gain) {
	if (!empty($starsgain[$gain["account"]])) {
		$starsgain[$gain["account"]] += $gain["value"];
	} else {
		$starsgain[$gain["account"]] = $gain["value"];
	}
}
arsort($starsgain);
foreach ($starsgain as $userID => $stars) {
	$query = $db->prepare("SELECT userName FROM users WHERE userID = :userID AND isLeaderboardBanned = 0 AND isBanned = 0 LIMIT 1");
	$query->execute([':userID' => $userID]);
	$userinfo = $query->fetch();
	$username = htmlspecialchars($userinfo["userName"], ENT_QUOTES);
	$x++;
	echo "<tr><td>$x</td><td>$userID</td><td>$username</td><td>$stars</td></tr>";
}  
?>
</table>