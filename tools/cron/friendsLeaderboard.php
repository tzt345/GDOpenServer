<?php
chdir(__DIR__);
set_time_limit(0);
$frndlog = "";
include "../../incl/lib/connection.php";
$query = $db->prepare("SELECT accountID, userName FROM accounts");
$query->execute();
$result = $query->fetchAll();
//getting accounts
foreach($result as $account){
	//getting friends count
	$me = $account["accountID"];
	$query2 = $db->prepare("SELECT count(*) FROM friendships WHERE person1 = :me OR person2 = :me");
	$query2->execute([':me' => $me]);
	$friendscount = $query2->fetchColumn();
	$frndlog .= $account["userName"] . " - " . $friendscount . "\r\n";
	//inserting friends count value
	if($friendscount != 0){
		echo htmlspecialchars($account["userName"],ENT_QUOTES) . " now has $friendscount friends... <br>";
		ob_flush();
		flush();
		$query4 = $db->prepare("UPDATE accounts SET friendsCount=:friendscount WHERE accountID=:me");
		$query4->execute([':friendscount' => $friendscount, ':me' => $me]);
	}
}
touch("../logs/frndlog.txt");
file_put_contents("../logs/frndlog.txt",$frndlog);
echo "<hr>";
?>
