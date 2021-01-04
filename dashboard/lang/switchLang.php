<?php
session_start();
include "../../incl/lib/connection.php";
require "../incl/dashboardLib.php";
$dl = new dashboardLib();
if(isset($_GET["lang"]) AND ctype_alpha($_GET["lang"])){
	$time = time() - 3600;
	setcookie("lang", strtoupper($_GET["lang"]), $time, __DIR__ . "/../../dashboard");
	setcookie("lang", strtoupper($_GET["lang"]), $time, __DIR__ . "/../../dashboard/lang");
	setcookie("lang", strtoupper($_GET["lang"]), 2147483647, "/");
	if(isset($_SERVER["HTTP_REFERER"])){
		header('Location: ' . $_SERVER["HTTP_REFERER"]);
	}
	$dl->printBox("<p>Language changed. <a href='index.php'>Click here to continue</a></p>");
}else{
	$dl->printBox("Invalid language. <a href='..'>Click here to continue</a></p>");
}
?>