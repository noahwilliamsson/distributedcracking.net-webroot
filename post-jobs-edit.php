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
		if(!isset($_GET["id"]) || (!isset($_POST["jobname"]) && !isset($_POST["delete"]))) {
			log_event("Missing data, job not modified");
			$_SESSION["error"] = "Missing data, job not modified";
			break;
		}

		$post_groups = array();
		if(isset($_POST["groups"]))
			$post_groups = $_POST["groups"];


		$job_id = intval($_GET["id"]);

                // Fetch the job if it exists
                $q = "SELECT * FROM jobs WHERE id='". $m->escape_string($job_id) ."'";
                if(($r = @$m->query($q)) !== FALSE) {
                        $job = $r->fetch_object();
                        $r->close();
                }

                if($job != NULL) {
                        $q = "SELECT groups.* FROM group_jobs LEFT JOIN groups ON group_id=groups.id WHERE job_id='". $m->escape_string($job_id) ."' ORDER BY groupname";
                        $q = "SELECT group_id AS id FROM group_jobs WHERE job_id='". $m->escape_string($job_id) ."'";
                        if(($r = @$m->query($q)) !== FALSE) {
                                while($row = $r->fetch_object())
                                        $job_groups[$row->id] = $row;
                                $r->close();
                        }
                }

                // If the user isn't the owner of the job check if he's member of
                // a group who have access to it
                if($job != NULL && $job->owner_user_id != $_SESSION["u"]->id) {
                        $user_has_access = FALSE;
                        foreach(user_get_groups($_SESSION["u"]->id) as $g) {
                                if(!isset($job_groups[$g->id]))
                                        continue;

                                $user_has_access = TRUE;
                                break;
                        }

                        if(!$user_has_access)
                                $job = NULL;
                }

		if(!$job) {
			$_SESSION["error"] = "Nonexistant job or access denied. God knows what and we're certainly not telling.";
			break;
		}



		// Should we delete the job?
		if(isset($_POST["delete"])) {
			// Set JOB_FLAG_DELETED
			$q = "UPDATE jobs SET jobflags = jobflags | ". (JOB_FLAG_DELETED) ." WHERE id='". $m->escape_string($job->id) ."'";
	                if(@$m->query($q) === FALSE) {
				$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
				break;
			}

			// Clear JOB_FLAG_ACTIVE
			$q = "UPDATE jobs SET jobflags = jobflags & ~". (JOB_FLAG_ACTIVE) ." WHERE id='". $m->escape_string($job->id) ."'";
	                if(@$m->query($q) === FALSE) {
				$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
				break;
			}

			$_SESSION["info"] = "Deleted job with name '". $job->jobname ."'";
			header("Location: $root_url"."jobs");
			die;
		}




		// Update job
		// XXX - Race condition with non-atomic upate of the job flags..
		$jobflags = $job->jobflags;
		if(($job->jobflags & JOB_FLAG_DONE) == 0 && isset($_POST["activeflag"])) {
			if($_POST["activeflag"] == 1)
				$jobflags |= JOB_FLAG_ACTIVE;
			else
				$jobflags &= ~JOB_FLAG_ACTIVE;

		}
		$q = "UPDATE jobs SET jobflags=$jobflags, jobname='". $m->escape_string($_POST["jobname"]) ."' WHERE id='". $m->escape_string($_GET["id"]) ."'";
                if(@$m->query($q) === FALSE) {
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}

		
		header("Location: $root_url"."jobs?id=$job_id");

	} while(0);
?>
