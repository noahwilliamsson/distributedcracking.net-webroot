<?php

	do {
		/*
		 * Take care of joining a team
		 * - Make sure the invitation code is correct
		 * - Make sure the user is not already a member of the team
		 * - Add the membership to the team
		 *
		 */
		if(isset($_POST["invite_code"])) {
			$q = "SELECT id FROM groups WHERE invite_code='". $m->escape_string(trim($_POST["invite_code"])) ."'";
			if(($r = @$m->query($q)) !== FALSE) {
				if(($team = $r->fetch_object()) !== NULL) {
					$q = "SELECT id FROM group_members WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."' AND  group_id='". $m->escape_string($team->id) ."'";
					if(($r_tm = @$m->query($q)) !== FALSE) {
						if($r_tm->num_rows == 0) {
							$q = "INSERT INTO group_members SET user_id='". $m->escape_string($_SESSION["u"]->id) ."', group_id='". $m->escape_string($team->id)  ."'";
							if(@$m->query($q) === FALSE) {
								log_event("Failed to find team with invitation code '". $_POST["invite_code"] ."'. MySQL: $m->error. SQL: $q");
								$_SESSION["error"] = "An internal database error occured. Please wait a while and try again.";
							}
						}
						else
							$_SESSION["info"] = "You're already a member of that team!";

						$r_tm->close();
					}
					else {
						log_event("Failed to find team with invitation code '". $_POST["invite_code"] ."'. MySQL: $m->error. SQL: $q");
						$_SESSION["error"] = "An internal database error occured. Please wait a while and try again.";
					}
				}
				else
					$_SESSION["error"] = "Invalid invitation code. Make sure you got it right.";

				$r->close();
			}
			else {
				log_event("Failed to find team with invitation code '". $_POST["invite_code"] ."'. MySQL: $m->error. SQL: $q");
				$_SESSION["error"] = "An internal database error occured. Please wait a while and try again.";
			}

			// Recalculate group memberships
			if(user_set_session_groups($_SESSION["u"]->id) === FALSE)
				log_event("Failed to recalculate groups");

			break;
		}


		/*
		 * Take care of creating a group
		 *
		 */
		if(!isset($_POST["g"]) || !isset($_GET["id"])) {
			break;
		}

		$q = "SELECT id FROM groups WHERE groupname='". $m->escape_string($_POST["g"]) ."'";
		$r = @$m->query($q);
		if($r->num_rows != 0) {
			$page_error = "You're out of luck. Somebody else already created a group called '". $_POST["g"] ."'. Try something different!";
			$r->close();
			break;
		}
		$r->close();


		// Create group
		$invite_code = md5(md5($_POST["g"]) . date("YmdHiS") . (string)mt_rand());
		$q = "INSERT INTO groups SET groupname='". $m->escape_string($_POST["g"]) ."', invite_code='". $m->escape_string($invite_code) ."'";
		if(@$m->query($q) === FALSE) {
			$page_error = "Sorry, an internal database error occured. Your group was NOT created. Wait a while and try again.";
			break;
		}

		$group_id = $m->insert_id;


		// Add membership to the new group
		$q = "INSERT INTO group_members SET group_id='". $m->escape_string($group_id) ."', user_id='". $m->escape_string($_SESSION["u"]->id) ."', group_admin=1";
		@$m->query($q);



		// Recalculate group memberships
		if(user_set_session_groups($_SESSION["u"]->id) === FALSE) {
			log_event("Failed to recalculate groups");
			break;
		}


		$_SESSION["info"] = "Created a group called '". $_POST["g"] ."'. Consider inviting other users to it!";
		log_event("User ". $_SESSION["u"]->username ." created a group called ". $_POST["g"]);

		header("Location: $root_url"."groups");

	} while(0);
?>
