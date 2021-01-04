<?php
require "../incl/lib/connection.php";
require "../config/connection.php";
$query = $db->prepare("SELECT `AUTO_INCREMENT`
	FROM  INFORMATION_SCHEMA.TABLES
	WHERE TABLE_SCHEMA = :database
	AND   TABLE_NAME   = 'songs';"); 
$query->execute([':database' => $dbname]);
echo $query->fetchColumn();
?>