<?php
require "../incl/lib/connection.php";
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();
$link = $_GET["link"];
$name = $_GET["name"];
$author = $_GET["author"];
$size = $gs->getFileSize($link);
$size = round($size / 1024 / 1024, 2);
$name = str_replace("#", "", $name);
$name = str_replace(":", "", $name);
$name = str_replace("~", "", $name);
$name = str_replace("|", "", $name);
$query = $db->prepare("SELECT count(*) FROM songs WHERE download = :download");
$query->execute([':download' => $link]);
$count = $query->fetchColumn();
if ($count != 0) {
	echo "This song already exists in our database.";
} else {
	$query = $db->prepare("INSERT INTO songs (name, authorID, authorName, size, download) VALUES (:name, 9, :author, :size, :download)");
	$query->execute([':name' => $name, ':download' => $link, ':author' => $author, ':size' => $size]);
	echo $db->lastInsertId();
}
?>