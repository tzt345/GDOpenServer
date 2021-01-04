<?php
class GJPCheck {
	public function check($gjp, $accountID) {
		require __DIR__ . "/connection.php";
		require __DIR__ . "/../../config/security.php";
		require_once __DIR__ . "/mainLib.php";
		$gs = new mainLib();
		if (!is_numeric($accountID) AND $accountID != "0") {
			return 0;
		}
		if ($sessionGrants) {
			$ip = $gs->getIP();
			if ($sessionGrantsTime <= 0 OR ($_POST["gameVersion"]) <= 19) {
				$query = $db->prepare("SELECT count(*) FROM actions WHERE type = 16 AND value = :accountID AND value2 = :ip");
				$query->execute([':accountID' => $accountID, ':ip' => $ip]);
			} else {
				$query = $db->prepare("SELECT count(*) FROM actions WHERE type = 16 AND value = :accountID AND value2 = :ip AND timestamp > :timestamp");
				$query->execute([':accountID' => $accountID, ':ip' => $ip, ':timestamp' => time() - ($sessionGrantsTime * 60)]);
			}
			if ($query->fetchColumn() > 0) {
				return 1;
			}
		}
		require_once __DIR__ . "/XORCipher.php";
		$xor = new XORCipher();
		require_once __DIR__ . "/generatePass.php";
		$gp = new generatePass();
		$gjpdecode = str_replace("_", "/", $gjp);
		$gjpdecode = str_replace("-", "+", $gjpdecode);
		$gjpdecode = base64_decode($gjpdecode);
		$gjpdecode = $xor->cipher($gjpdecode, 37526);
		$pass = $generatePass->isValid($accountID, $gjpdecode);
		if ($pass == 1 AND $sessionGrants) {
			$ip = $gs->getIP();
			$query = $db->prepare("INSERT INTO actions (type, value, value2, timestamp) VALUES (16, :accountID, :ip, :timestamp)");
			$query->execute([':accountID' => $accountID, ':ip' => $ip, ':timestamp' => time()]);
			return 1;
		}
		return $pass;
	}
}
?>
