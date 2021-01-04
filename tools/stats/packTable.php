<h1>MAP PACKS</h1>
<table border="1"><tr><th>#</th><th>ID</th><th>Map Pack</th><th>Stars</th><th>Coins</th><th>Levels</th></tr>
<?php
require "../../incl/lib/connection.php";
$x = 1;
$query = $db->prepare("SELECT * FROM mappacks ORDER BY ID ASC");
$query->execute();
$result = $query->fetchAll();
foreach ($result as &$pack) {
	$lvlarray = explode(",", $pack["levels"]);
	echo "<tr><td>$x</td><td>" . $pack["ID"] . "</td><td>" . htmlspecialchars($pack["name"], ENT_QUOTES) . "</td><td>" . $pack["stars"] . "</td><td>" . $pack["coins"] . "</td><td>";
	$x++;
	foreach ($lvlarray as &$lvl) {
		echo $lvl . " - ";
		$query = $db->prepare("SELECT levelName FROM levels WHERE levelID = :levelID");
		$query->execute([':levelID' => $lvl]);
		$levelName = $query->fetchColumn();
		echo $levelName . ", ";
	}
	echo "</td></tr>";
}
/*
	Map packs
*/
?>
</table>
<h1>GAUNTLETS</h1>
<table border="1"><tr><th>#</th><th>Name</th><th>Level 1</th><th>Level 2</th><th>Level 3</th><th>Level 4</th><th>Level 5</th></tr>
<?php
require "../../incl/lib/connection.php";
require_once "../../incl/lib/mainLib.php";
$gs = new mainLib();
$query = $db->prepare("SELECT * FROM gauntlets ORDER BY ID ASC");
$query->execute();
$result = $query->fetchAll();
foreach ($result as &$gauntlet) {
	$gauntletname = $gs->getGauntletName($gauntlet["ID"]);
	echo "<tr><td>" . $gauntlet["ID"] . "</td><td>" . $gauntletname . "</td>";
	for ($x = 1; $x < 6; $x++) {
		echo "<td>";
		$lvl = $gauntlet["level" . $x];
		echo $lvl . " - ";
		$query = $db->prepare("SELECT levelName FROM levels WHERE levelID = :levelID");
		$query->execute([':levelID' => $lvl]);
		$levelName = $query->fetchColumn();
		echo "$levelName</td>";
	}
	echo "</tr>";
}
/*
	Gauntlets
*/
?>
</table>