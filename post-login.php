<?php

	if(isset($_POST["u"]) && isset($_POST["p"])) {
		$q = "SELECT * FROM users WHERE username='". $m->escape_string($_POST["u"]) ."' AND password='". $m->escape_string($_POST["p"]) ."'";
		if(($r = @$m->query($q)) !== FALSE) {
			$row = @$r->fetch_object();
			$r->close();

			if($row !== NULL) {
				$_SESSION["loggedin"] = TRUE;
				$_SESSION["u"] = $row;

				$q = "UPDATE users SET dt_lastlogin=NOW() WHERE id='". $m->escape_string($row->id) ."'";
				@$m->query($q);


				user_set_session_groups($row->id);
/*
				$_SESSION["groups"] = array();
				$q = "SELECT groups.*, group_admin FROM group_members LEFT JOIN groups ON group_id=groups.id WHERE user_id='". $m->escape_string($row->id) ."' ORDER BY groupname";
				if(($r = @$m->query($q)) !== FALSE) {
					while($row = $r->fetch_object())
						$_SESSION["groups"][$row->id] = array("group" => $row->groupname, "admin" => $row->group_admin, "dt_created" => $row->dt_created,  "invite_code" => $row->invite_code);
					$r->close();
				}
				else
					log_event("Login groups database error: $m->error. SQL: $q");
*/

				

				// Extract saved session cache, used for storing number of cracked hashes
				// for jobs for example
				$q = "SELECT * FROM sessioncache WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
				if(($r = @$m->query($q)) !== FALSE) {
					if($r->num_rows == 0) {
						$q = "INSERT INTO sessioncache SET user_id='". $m->escape_string($_SESSION["u"]->id) ."', session='". $m->escape_string(serialize(array())) ."'";
						@$m->query($q);

						$_SESSION["c"] = array();
					}
					else {
						$row = $r->fetch_object();
						$_SESSION["c"] = unserialize($row->session);
					}
					$r->close();
				}



				log_event("Login succeeded for user '". $_POST["u"] ."'");
				header("Location: $root_url");
			}
			else {
				$page_error = "Oops, login failed. Make sure you typed in the right username and password.";
				log_event("Login failed for user '". $_POST["u"] ."'");
			}

		}
		else {
			log_event("Login database error: $m->error. SQL: $q");
			$page_error = "Sorry, an internal database error occured. Try again in a bit!";
		}
	}
?>
