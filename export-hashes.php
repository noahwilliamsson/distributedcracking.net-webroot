<?php
	/*
	 * This file is responsible for downloading the hashes
	 * to the user
	 *
	 */



	do {
		if(!isset($_GET["id"])) {
			log_event("Missing data, hashes not exported");
			$_SESSION["error"] = "Missing data, hashes not exported";
			break;
		}

		$job_id = intval($_GET["id"]);


		$q = "SELECT * FROM jobs WHERE id='". $m->escape_string($job_id) ."'";
		if(($r = @$m->query($q)) === FALSE) {
			log_event("Database error while fetching job information for job id $job_id: ". $m->error);
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}

		if(($job = $r->fetch_object()) == NULL) {
			log_event("Unable to find job with id $job_id");
			$_SESSION["error"] = "Job not found.";
			break;
		}
		$r->close();

		// Make sure the user has access to this job
		if($job->owner_user_id != $_SESSION["u"]->id) {
			$q = "SELECT * FROM group_jobs WHERE job_id='". $m->escape_string($job_id) ."' AND group_id in('". implode("','", array_keys($_SESSION["groups"])) ."')";
			if(($r = @$m->query($q)) === FALSE) {
				log_event("Database error while fetching group data for job id $job_id: ". $m->error);
				$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
				break;
			}

			if($r->num_rows == 0) {
				$r->close();
				log_event("No access to job to $job_id: ". $m->error);
				$_SESSION["error"] = "You don't seem to have access to that job. Pushing your luck, eh?";
				break;
			}
			$r->close();
		}

		$m->set_charset("latin1");
		$q = "SELECT hash, plaintext FROM hashes WHERE job_id='". $m->escape_string($job_id) ."' AND plaintext IS NOT NULL";
		if(($r = @$m->query($q)) === FALSE) {
			log_event("Database error while fetching hashes for job id $job_id: ". $m->error);
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=\"$job->jobname (distributedcracking.net id $job_id).txt\"");
		while($row = $r->fetch_object()) {
			echo $row->hash .":". $row->plaintext ."\r\n";
		}

		$r->close();
		die;

	} while(0);
?>
