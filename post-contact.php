<?php
	
	/*
	 * This file is responsible for relaying contact requests
	 *
	 */



	do {
		if(!isset($_POST["text"])) {
			log_event("Missing data, message NOT sent");
			$_SESSION["error"] = "Missing data, message NOT sent";
			break;
		}

		$subject = "Contact request from the internet";
		if(isset($_SESSION["u"]))
			$subject = "Contact request from user ". $_SESSION["u"]->username;

		$sender = $contact_mail_from;
		if(isset($_POST["from"]))
			$sender = $_POST["from"];

		mail($contact_mail_to, $subject, $_POST["text"], "From: $sender");
		$_SESSION["info"] = "Contact message sent to site administrators. We'll get back to you if you supplied an email address.";

		header("Location: $root_url");

	} while(0);
?>
?>
