<?php
	/*
	 * XXX - Make sure the user is indeed a member of $_GET["id"]
	 * Otherwise, display the page for creating a new group. :>
	 *
	 */

	$group = NULL;
	$group_members = array();
	if(($gid = intval($_GET["id"])) > 0) {

		// Fetch group info
		$q = "SELECT * FROM groups WHERE id='". $m->escape_string($gid) ."'";
		if(($r = @$m->query($q)) !== FALSE) {
			$group = $r->fetch_object();
			$r->close();
		}


		// Fetch group members
		$user_found_in_group = FALSE;
		$q = "SELECT * FROM group_members WHERE group_id='". $m->escape_string($gid) ."'";
		if(($r = @$m->query($q)) !== FALSE) {

			// If the user is not a member of the group, $group_members will be empty
			// Otherwise it will contain information about the group's members
			while($row = $r->fetch_object()) {
				$group_members[$row->user_id] = $row;

				// Signal that we're indeed a member of this group!
				if($row->user_id == $_SESSION["u"]->id)
					$user_found_in_group = TRUE;
			}

			$r->close();
		}

		// Empty the array if the user wasn't among the group's members
		if(!$user_found_in_group)
			$group_members = array();
		else if(isset($_GET["remove"])
			&& ($_GET["id"] != 1 || ($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == USER_FLAG_PREMIUM)
			&& ($_GET["remove"] == $_SESSION["u"]->id || $_SESSION["groups"][$_GET["id"]]["admin"] == 1)) {

			$q = "DELETE FROM group_members WHERE group_id='". $m->escape_string($_GET["id"]) ."' AND user_id='". $m->escape_string($_GET["remove"]) ."'";
			if(@$m->query($q) === FALSE) {
				log_event("Failed to remove user ". $_GET["remove"] ." from group ". $_GET["id"] .": $m->error.\nSQL: $q");
				$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			}
			else if($m->affected_rows == 0) {
				$_SESSION["error"] = "The user was not a member of the group specified and weren't removed.";
			}
			else {
				// Stop sharing the user's jobs with the removed group
				$q = "SELECT job_id, group_id FROM jobs LEFT JOIN group_jobs ON jobs.id=job_id WHERE owner_user_id='". $m->escape_string($_GET["remove"]) ."' AND group_id='". $m->escape_string($_GET["id"]) ."'";
				if(($r = @$m->query($q)) !== FALSE) {
					while($row = $r->fetch_object()) {
						$q = "DELETE FROM group_jobs WHERE group_id=$row->group_id AND job_id=$row->job_id";
						@$m->query($q);
					}
				}


				if($_GET["remove"] == $_SESSION["u"]->id) {
					// Refresh group information
					$_SESSION["groups"] = array();
					foreach(user_get_groups($_SESSION["u"]->id) as $g) {
						$_SESSION["groups"][$g->id] = array("group" => $g->groupname, "admin" => $g->group_admin, "dt_created" => $g->dt_created,  "invite_code" => $g->invite_code);
					}


					$_SESSION["info"] = "You left the group ". $_SESSION["groups"][$_GET["id"]]["group"];
					header("Location: $root_url"."groups");
					die;
				}
				else
					$_SESSION["info"] = "User was removed from the group ". $_SESSION["groups"][$_GET["id"]]["group"];
			}
		}
		else if(isset($_GET["remove"])) {
			if($_GET["id"] == 1)
				$_SESSION["error"] = "You cannot remove yourself from the Public group with a standard account. Please upgrade to a premium account.";
			else
				$_SESSION["error"] = "Access denied. Nonexistant group or user, or you're not a group administrator of the group specified.";
		}
	}
?>
