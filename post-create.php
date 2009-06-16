<?php

	do {
		if(!isset($_POST["u"]) || !isset($_POST["p"])) {
			break;
		}

		if($invite_only && (!isset($_POST["i"]) || $_POST["i"] != "beta")) {
			$page_error = "You're out of luck. An invalid invite code was supplied. Have a friend hook you up with one or contact us to get one.";
			break;
		}

		$q = "SELECT id FROM users WHERE username='". $m->escape_string($_POST["u"]) ."'";
		$r = @$m->query($q);
		if($r->num_rows != 0) {
			$page_error = "You're out of luck. Somebody else already goes by the name '". $_POST["u"] ."'. Try something different!";
			$r->close();
			break;
		}
		$r->close();


		// Create user
		$q = "INSERT INTO users SET username='". $m->escape_string($_POST["u"]) ."', password='". $m->escape_string($_POST["p"]) ."', dt_lastlogin=NOW()";
		if(@$m->query($q) === FALSE) {
			$page_error = "Sorry, an internal database error occured. Your account was NOT created. Wait a while and try again.";
			break;
		}

		// Add membership to the public group
		$q = "INSERT INTO group_members SET group_id=1, user_id='". $m->escape_string($m->insert_id) ."'";
		@$m->query($q);


		// Login user
		$q = "SELECT * FROM users WHERE username='". $m->escape_string($_POST["u"]) ."'";
		$r = @$m->query($q);
		$row = $r->fetch_object();
		$r->close();

		$_SESSION["loggedin"] = TRUE;
		$_SESSION["u"] = $row;

		$_SESSION["groups"] = array();
		$q = "SELECT groups.*, group_admin FROM group_members LEFT JOIN groups ON group_id=groups.id WHERE user_id='". $m->escape_string($row->id) ."'";  
		if(($r = @$m->query($q)) !== FALSE) {
			while($row = $r->fetch_object())
				$_SESSION["groups"][$row->id] = array("group" => $row->groupname, "admin" => $row->group_admin);
			$r->close();
		}


		log_event("User ". $_SESSION["u"]->username ." (id ". $_SESSION["u"]->id .") registered");


		header("Location: $root_url");

	} while(0);
?>
