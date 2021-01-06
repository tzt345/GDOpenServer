<a href="../dashboard">Check out the dashboard beta here</a>
<?php
function listdir($dir) {
	$dirstring = "";
	$files = scandir($dir);
	foreach ($files as $file) {
		if (pathinfo($file, PATHINFO_EXTENSION) == "php" AND $file != "index.php") {
			$dirstring .= "<li><a href='$dir/$file'>$file</a></li>";
		}
	}
	return $dirstring;
}
echo '<h1>Account management tools:</h1><ul>';
echo "<li><a href='account/index.php'>Account Management</a></li>";
echo'</ul><h1>Upload related tools:</h1><ul>';
echo listdir(".");
echo "</ul><h1>The cron job (fixing CPs, auto-ban, etc.)</h1><ul>";
echo "<li><a href='cron/cron.php'>cron.php</a></li>";
echo'</ul><h1>Clean up tools (USE AT YOUR OWN RISK):</h1><ul>';
echo listdir("cleanup");
echo "</ul><h1>Stats related tools</h1><ul>";
echo listdir("stats");
?>