<?php
session_start();
require "../incl/lib/connection.php";
require "../config/metadata.php";
require "../config/levels.php";
require_once "incl/dashboardLib.php";
$dl = new dashboardLib();
require_once "../incl/lib/mainLib.php";
$gs = new mainLib();

$chartdata = array();
for ($x = 7; $x >= 0;) {
	$timeBefore = time() - (86400 * $x);
	$timeAfter = time() - (86400 * ($x + 1));
	if ($showCreatorBannedPeoplesLevels == 1) {
		$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate < :timeBefore AND uploadDate > :timeAfter AND userID != :botUID AND extID != :botAID");
	} else {
		$query2 = $db->prepare("SELECT userID FROM users WHERE isCreatorBanned = 1");
		$query2->execute();
		$banResult = $query2->fetch();
		$bannedPeople = "";
		foreach ($banResult as &$bannedPerson) {
			$bannedPeople .= $bannedPerson["roleID"] . ",";
		}
		$bannedPeople .= $gs->getBotUserID();
		$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate < :timeBefore AND uploadDate > :timeAfter AND ( userID NOT IN ($bannedPeople) ) AND extID != :botAID");
	}
	$query->execute([':timeBefore' => $timeBefore, ':timeAfter' => $timeAfter, ':botUID' => $gs->getBotUserID(), ':botAID' => $gs->getBotAccountID()]);
	switch ($x) {
		case 1:
			$identifier = $x . " day ago";
			break;
		case 0:
			$identifier = "Last 24 hours";
			break;
		default:
			$identifier = $x . " days ago";
			break;
	}
	$chartdata[$identifier] = $query->fetchColumn();
	$x--;
}

$levelsChart2 = array();
$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$x = 0;
foreach ($months as &$month) {
	$x++;
	$nextMonthYear = date('Y');
	if ($x == 12) {
		$x = 0;
		$nextMonthYear++;
	}
	$nextMonth = $months[$x];
	$timeBefore = strtotime("first day of $month " . date('Y'));
	$timeAfter = strtotime("first day of $nextMonth " . $nextMonthYear);
	if ($showCreatorBannedPeoplesLevels == 1) {
		$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate > :timeBefore AND uploadDate < :timeAfter AND userID != :botUID AND extID != :botAID");
	} else {
		$query2 = $db->prepare("SELECT userID FROM users WHERE isCreatorBanned = 1");
		$query2->execute();
		$banResult = $query2->fetch();
		$bannedPeople = "";
		foreach ($banResult as &$bannedPerson) {
			$bannedPeople .= $bannedPerson["roleID"] . ",";
		}
		$bannedPeople .= $gs->getBotUserID();
		$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate > :timeBefore AND uploadDate < :timeAfter AND ( userID NOT IN ($bannedPeople) ) AND extID != :botAID");
	}
	$query->execute([':timeBefore' => $timeBefore, ':timeAfter' => $timeAfter, ':botUID' => $gs->getBotUserID(), ':botAID' => $gs->getBotAccountID()]);
	$amount = $query->fetchColumn();
	if ($amount != 0) {
		$levelsChart2[$month] = $amount;
	}
}

$dl->printPage('<p>Welcome to the ' . $gdpsName . ' dashboard. Please choose a tool above.
				<br>DISCLAIMER: THIS AREA IS UNDER HEAVY DEVELOPEMENT, DON\'T EXPECT MUCH STUFF TO WORK
				<br>Legend: (N) = Not Working, (T) = Links to the legacy tool version
				<br>
					<div class="chart-container" style="position: relative; height:30vh; width:80vw">
						<canvas id="levelsChart"></canvas>
					</div>
				<br>
					<div class="chart-container" style="position: relative; height:30vh; width:80vw">
						<canvas id="levelsChart2"></canvas>
					</div>
				</p>' . $dl->generateLineChart("levelsChart","Levels Uploaded", $chartdata) . $dl->generateLineChart("levelsChart2", "Levels Uploaded", $levelsChart2), false);
?>