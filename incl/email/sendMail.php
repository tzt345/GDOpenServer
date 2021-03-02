<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PMsrc/Exception.php';
require __DIR__ . '/PMsrc/PHPMailer.php';
require __DIR__ . '/PMsrc/SMTP.php';

function sendVerificationMail($email, $secret, $accountID) {
	require "../../config/email.php";
	require "../../config/metadata.php";
	$mail = new PHPMailer(TRUE);
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";

	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Port = $port;
	$mail->Username = $username;
	$mail->Password = $password;
	$mail->isHTML(true);
	$mail->Subject = "Please verify your $gdpsName account.";
	$mail->From = $from;
	$mail->FromName = "$gdpsName";
	$mail->addAddress($email);
	$URL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// uhhhhh don't ask, I don't know how to think properply
	$URL_array = explode("/", $URL);
	array_splice($URL_array, -1);
	array_splice($URL_array, -1);
	if (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on') {
		$URL = "https://";
	} else {
		$URL = "http://";
	}
	foreach ($URL_array as $dir) {
		$URL .= $dir . "/";
	}
	$URL .= "account/verifyAccount.php?secret=$secret&ID=$accountID";
	// beautifulL - you may change the html part below in the way you like, but please keep in mind to include atleast a link referring to $URL.
	$mail->Body = "<center>
		<div style='width=80%;height=80%;border: 2px solid #000;'>
			<span style='font-family: Verdana;'>
			<strong>Thank you for registering on $gdpsName!</strong>
			<br></span><span style='font-family: Verdana;'>To verify your account, please click on the link below.<br>
			<a href='$URL' target='_blank' rel='noopener'>Verify Account Creation.</a><br></span>
			<strong><span style='color: #0000ff;'><span style='font-family: Verdana;'>Enjoy playing on our server!</span></span></strong>
		</div>
		<span style='font-size: x-small;'>This is not a Newsletter E-Mail and was only sent to you because you have registered on $gdpsName.<br>
		Please do not reply.</span></center><br>";
	$mail->AltBody = "You received this E-Mail because you registered on $gdpsName. Please verify your account using the following link: $URL";
	$mail->send();
}

function sendPasswordRecoverEmail() {
	// uhhhhh not done yet
}

function sendBanEmail() {
	require "../../config/email.php";
	require "../../config/metadata.php";
	$mail = new PHPMailer(TRUE);
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";

	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Port = $port;
	$mail->Username = $username;
	$mail->Password = $password;
	$mail->isHTML(true);
	$mail->Subject = "Please verify your $gdpsName account.";
	$mail->From = $from;
	$mail->FromName = "$gdpsName";
	$mail->addAddress($email);
	$URL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// uhhhhh don't ask, I don't know how to think properply
	$URL_array = explode("/", $URL);
	array_splice($URL_array, -1);
	array_splice($URL_array, -1);
	if (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on') {
		$URL = "https://";
	} else {
		$URL = "http://";
	}
	foreach ($URL_array as $dir) {
		$URL .= $dir . "/";
	}
	$URL .= "account/verifyAccount.php?secret=$secret&ID=$accountID";
	$mail->Body = "<center>
		<div style='width=80%;height=80%;border: 2px solid #000;'>
			<span style='font-family: Verdana;'>
			<strong>Thank you for registering on $gdpsName!</strong>
			<br></span><span style='font-family: Verdana;'>To verify your account, please click on the link below.<br>
			<a href='$URL' target='_blank' rel='noopener'>Verify Account Creation.</a><br></span>
			<strong><span style='color: #0000ff;'><span style='font-family: Verdana;'>Enjoy playing on our server!</span></span></strong>
		</div>
		<span style='font-size: x-small;'>This is not a Newsletter E-Mail and was only sent to you because you have registered on $gdpsName.<br>
		Please do not reply.</span></center><br>";
	$mail->AltBody = "You received this E-Mail because you registered on $gdpsName. Please verify your account using the following link: $URL";
	$mail->send();
}

function sendAnnouncementEmail() {
	require "../../config/email.php";
	require "../../config/metadata.php";
	$mail = new PHPMailer(TRUE);
	$mail->IsSMTP();
	$mail->CharSet = "UTF-8";

	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Port = $port;
	$mail->Username = $username;
	$mail->Password = $password;
	$mail->isHTML(true);
	$mail->Subject = "Please verify your $gdpsName account.";
	$mail->From = $from;
	$mail->FromName = "$gdpsName";
	$mail->addAddress($email);
	$URL = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// uhhhhh don't ask, I don't know how to think properply
	$URL_array = explode("/", $URL);
	array_splice($URL_array, -1);
	array_splice($URL_array, -1);
	if (isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] === 'on') {
		$URL = "https://";
	} else {
		$URL = "http://";
	}
	foreach ($URL_array as $dir) {
		$URL .= $dir . "/";
	}
	$URL .= "account/verifyAccount.php?secret=$secret&ID=$accountID";
	$mail->Body = "<center>
		<div style='width=80%;height=80%;border: 2px solid #000;'>
			<span style='font-family: Verdana;'>
			<strong>Thank you for registering on $gdpsName!</strong>
			<br></span><span style='font-family: Verdana;'>To verify your account, please click on the link below.<br>
			<a href='$URL' target='_blank' rel='noopener'>Verify Account Creation.</a><br></span>
			<strong><span style='color: #0000ff;'><span style='font-family: Verdana;'>Enjoy playing on our server!</span></span></strong>
		</div>
		<span style='font-size: x-small;'>This is not a Newsletter E-Mail and was only sent to you because you have registered on $gdpsName.<br>
		Please do not reply.</span></center><br>";
	$mail->AltBody = "You received this E-Mail because you registered on $gdpsName. Please verify your account using the following link: $URL";
	$mail->send();
}
?>

