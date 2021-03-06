<?php
class GJPCheck {
	public function check($gjp, $accountID) {
		include __DIR__."/connection.php";
		include __DIR__."/../../config/security.php";
		include_once __DIR__."/mainLib.php";
		$ml = new mainLib();
		if($sessionGrants){
			$ip = $ml->getIP();
			if ($sessionGrantsTime <= 0 OR ($_POST["gameVersion"]) <= 19) {
				$query = $db->prepare("SELECT count(*) FROM actions WHERE type = 16 AND value = :accountID AND value2 = :ip");
				$query->execute([':accountID' => $accountID, ':ip' => $ip]);
				if($query->fetchColumn() > 0){
					return 1;
				}
			} else {
				$query = $db->prepare("SELECT count(*) FROM actions WHERE type = 16 AND value = :accountID AND value2 = :ip AND timestamp > :timestamp");
				$query->execute([':accountID' => $accountID, ':ip' => $ip, ':timestamp' => time() - ($sessionGrantsTime * 60)]);
				if($query->fetchColumn() > 0){
					return 1;
				}
			}
		}
		require_once __DIR__."/XORCipher.php";
		require_once __DIR__."/generatePass.php";
		$xor = new XORCipher();
		$gjpdecode = str_replace("_", "/", $gjp);
		$gjpdecode = str_replace("-", "+", $gjpdecode);
		$gjpdecode = base64_decode($gjpdecode);
		$gjpdecode = $xor->cipher($gjpdecode, 37526);
		$generatePass = new generatePass();
		if($generatePass->isValid($accountID, $gjpdecode) == 1 AND $sessionGrants){
			$ip = $ml->getIP();
			$query = $db->prepare("INSERT INTO actions (type, value, value2, timestamp) VALUES (16, :accountID, :ip, :timestamp)");
			$query->execute([':accountID' => $accountID, ':ip' => $ip, ':timestamp' => time()]);
			return 1;
		}
		return $generatePass->isValid($accountID, $gjpdecode);
	}
}
?>
