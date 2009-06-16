<?php
	/*
	 * This file is responsible for creating new jobs
	 *
	 * The following tables are modified
	 * - jobs (new entry)
	 * - user_job_map (connect the user with the new job)
	 * - hashes (hashes are added and connected to the new job)
	 *
	 */



	do {
		if(!isset($_GET["id"]) || !isset($_POST["nodename"])) {
			$_SESSION["error"] = "Missing data, node not modified";
			break;
		}

		$q = "SELECT id FROM nodes WHERE id='". $m->escape_string($_GET["id"]) ."' AND user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
		if(($r = @$m->query($q)) === FALSE) {
			log_event("Database error while searching for node: ". $m->error);
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}
		else if(($row = $r->fetch_object()) == NULL) {
			$_SESSION["error"] = "Nonexistant node or access denied.";
			$r->close();
			break;
		}
		$r->close();


		$q = "UPDATE nodes SET nodename='". $m->escape_string($_POST["nodename"]) ."' WHERE id='". $m->escape_string($_GET["id"]) ."'";
		if(($r = @$m->query($q)) === FALSE) {
			log_event("Database error while updating node: ". $m->error);
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}

		header("Location: $root_url"."nodes?id=". $_GET["id"]);
		
	} while(0);
?>
