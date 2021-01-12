<?php
require_once __DIR__ . "/../lib/exploitPatch.php";
$ep = new exploitPatch();
if (!empty($_GET["songID"])) {
    $songID = $ep->remove($_GET["songID"]);
} else {
    exit("-1");
}
$url = 'http://www.boomlings.com/database/testSong.php';
$data = array(
	'songID' => $songID,
);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($ch);
curl_close($ch);
echo $result;
?>