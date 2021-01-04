<?php
chdir(__DIR__);
require "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
if (isset($_POST["levelID"])) {
	$levelID = $ep->remove($_POST["levelID"]);
	$ip = $gs->getIP();
	$query = $db->prepare("SELECT count(*) FROM reports WHERE levelID = :levelID AND hostname = :hostname");
	$query->execute([':levelID' => $levelID, ':hostname' => $ip]);
	if ($query->fetchColumn() == 0) {
		$query = $db->prepare("INSERT INTO reports (levelID, hostname) VALUES (:levelID, :hostname)");	
		$query->execute([':levelID' => $levelID, ':hostname' => $ip]);
		echo $db->lastInsertId();
	} else {
		echo "-1";
	}	
} else {
	echo "-1";
}
?>