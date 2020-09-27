<?php
ob_start();
require "../config/metadata.php";
if ($usePatcher == 0) {
	exit("This tool is disabled! Re-enable it in /config/metadata.php");
}
if (isset($_FILES['userfile'])) {
	if ($_FILES['userfile']['error'] == UPLOAD_ERR_OK AND is_uploaded_file($_FILES['userfile']['tmp_name'])) {
		if (!$_FILES['userfile']['name'] == "GeometryDash.exe") {
			exit("That's probably not the correct Geometry Dash game file.");
		}
		$uploadedFile = fopen($_FILES['userfile']['tmp_name'], "rb");
		$content = stream_get_contents($uploadedFile);
		function fixLength($str, $minLen) {
			if (strlen($str) == $minLen) {
				return $str;
			} elseif (strlen($str) > $minLen) {
				exit("$str is too long! Please make sure the URL is shorter or equal the length of $minLen.");
			}
			$len = $minLen - (strlen($str));
			$expl = explode('/', $str);
			$lastfolder = str_repeat("/", $len);
			$lastfolder.= $expl[count($expl) - 1];
			return substr($str, 0, -(strlen($expl[count($expl) - 1]))) . $lastfolder;
		}
		// Fix URL length and convert them to hex if needed
		$gdps_url = $_SERVER['HTTP_HOST'];   
		$gdps_url .= $_SERVER['REQUEST_URI'];
		$gdps_url_array = explode("/", $gdps_url);
		unset($gdps_url_array[-1]);
		unset($gdps_url_array[-1]);
		if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on') {
			$gdps_url = "https://";
		} else {
			$gdps_url = "http://";
		}
		foreach($gdps_url_array as $dir) {
			$gdps_url .= $dir."/";
		}
		$gdps_url = substr($gdps_url, 0, -1);
		$gdps_url = fixLength($gdps_url, 33);
		$youtube = bin2hex(fixLength($youtube, 40)) . "00";
		$twitter = bin2hex(fixLength($twitter, 31)) . "00";
		$facebook = bin2hex(fixLength($facebook, 37)) . "00";
		$robtopWebsite = bin2hex(fixLength($robtopWebsite, 26)) . "00";
		// Replace Links
		$content = str_replace("http://www.boomlings.com/database", $gdps_url, $content);
		$content = str_replace(base64_encode("http://www.boomlings.com/database"), base64_encode($gdps_url), $content);
		// Replace Hex-Encoded Strings (for compatibility).
		// YouTube
		$hexContent = str_replace("68747470733A2F2F7777772E796F75747562652E636F6D2F757365722F526F62546F7047616D657300", $youtube, bin2hex($content));
		// Twitter
		$hexContent = str_replace("68747470733A2F2F747769747465722E636F6D2F726F62746F7067616D657300", $twitter, $hexContent);
		// Facebook
		$hexContent = str_replace("68747470733A2F2F7777772E66616365626F6F6B2E636F6D2F67656F6D657472796461736800", $facebook, $hexContent);
		// RobTop's website
		$hexContent = str_replace("687474703A2F2F7777772E726F62746F7067616D65732E636F6D00", $robtopWebsite, $hexContent);
		// Check wether you need to verify your E-Mail or not...
		if ($verifyMail == 0) {
			$hexContent = str_replace("4120636F6E6669726D6174696F6E20656D61696C20686173206265656E2073656E7420746F20796F757220696E626F78203C63793E25733C2F633E2E0A506C65617365203C63673E61637469766174653C2F633E20796F7572206163636F756E742E", "596F7520617265206E6F7420726571756972656420746F2076657269667920796F757220452D4D61696C2E0A596F752063616E206E6F77206C6F67696E2C20656E6A6F7920706C6179696E67206F6E20746865204744505321000000000000000000", $hexContent);
		}
		// Finally, download.
		ob_end_clean();
		header("Content-Description: File Transfer");
		header("Content-Type: application/octet-stream");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Disposition: attachment; filename=\"$gdps_name.exe\"");
		exit(hex2bin($hexContent));
	} else {
		ob_end_clean();
		if ($_FILES['userfile']['error'] != 0) {
			echo "Error: " . $_FILES['userfile']['error'];
		}
	}
} else {
?>

<form enctype="multipart/form-data" action="" method="POST">
	Upload GeometryDash.exe please → <input name="userfile" type="file" /><br>
	<input type="submit" value="Patch" />
</form>

<?php } ?>