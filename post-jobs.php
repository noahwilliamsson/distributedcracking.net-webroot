<?php
	/*
	 * This file is responsible for creating new jobs
	 *
	 * The following tables are modified
	 * - jobs (new entry)
	 * - user_job_map (connect the user with the new job)
	 * - hashes (hashes are added and connected to the new job)
	 *
         * TODO:
         * - Create one job for each type of hash if we're sent a mixed list
         * - Deal with duplicate hashes; two binary equal hashes should only
         *   result in one entry.
	 */



	do {
		if(!isset($_FILES["hashfile"]) || $_FILES["hashfile"]["error"] != 0 || !isset($_POST["mode"]) || !isset($_POST["jobname"])) {
			log_event("Missing data, job not created");
			$_SESSION["error"] = "Missing data, job not created";
			break;
		}

		if(!isset($attack_modes[$_POST["mode"]])) {
			log_event("Invalid mode '". $_POST["mode"] ."'");
			$_SESSION["error"] = "Invalid attack mode";
			break;
		}

		$post_groups = array();
		if(isset($_POST["groups"]))
			$post_groups = $_POST["groups"];


		// Log info about the file and move it to a temporary directory
		$f = $_FILES["hashfile"];
		log_event("File uploaded, size=". $f["size"] .", original name=". $f["name"] ." (". $f["type"] .")");


		$filename = $temp_dir . "hashfile-userid-". $_SESSION["u"]->id ."-". date("Ymd-His");
		if(@move_uploaded_file($f["tmp_name"], $filename) === FALSE) {
			log_event("Error moving uploaded file to temporary directory. Permission problem on '$temp_dir'?");
			$_SESSION["error"] = "Sorry, an internal permission problem error occured. Please notify an administrator.";
			break;
		}


		// A cookie used when sharing jobs
		$jobcookie = md5(date("YmdHiS") . $f["size"] . $f["name"] . $f["type"] . $_SESSION["u"]->id . $_SERVER["REMOTE_ADDR"]);


		// Create a job before importing the hashes
		$q = "INSERT INTO jobs SET jobname='". $m->escape_string($_POST["jobname"]) ."', attack_mode='". $m->escape_string($_POST["mode"]) ."', jobcookie='". $m->escape_string($jobcookie) ."', owner_user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
		if(@$m->query($q) === FALSE) {
			log_event("Database error while creating job entry: ". $m->error);
			$_SESSION["error"] = "Sorry, an internal database error occured. Wait a while and try again.";
			break;
		}

		$job_id = $m->insert_id;


		// Open the hashfile and import the hashes
		if(($fd = fopen($filename, "rt")) === FALSE) {
			log_event("Failed to open uploaded hashfile '$filename'");
			$_SESSION["error"] = "Failed to open uploaded hashfile";
			break;
		}

		$num_entries = 0;
		$hashtype = "";
		$hashstats = array();
		while($line = fgets($fd)) {
			$line = trim($line);
			$hash = $line;
			if(strchr($line, ":")) {
				$temp = explode(":", $line, 3);
				$hash = $temp[1];
			}

			if(empty($hash))
				continue;
		
			if(preg_match("/^[0-9a-f]{32}$/i", $hash)) {
				$hashtype = "raw-MD5";
				$hash = strtolower($hash);
			}
			else if(preg_match("/^[0-9a-f]{40}$/i", $hash)) 
				$hashtype = "raw-sha1";
			else if(preg_match("/^[0-9a-f]{48}$/i", $hash)) 
				$hashtype = "macosx-sha1";
			else if(preg_match("/^[0-9a-f]{16}$/i", $hash)) {
				$hashtype = "mysql-fast";
				$hash = strtolower($hash);
			}
			else if(preg_match("/^[0-9a-zA-Z\/\.]{13}$/", $hash))
				$hashtype = "DES";
			else if(!strncmp($hash, '$1$', 3))
				$hashtype = "MD5";
			else if(!strncmp($hash, '$apr1$', 6))
				$hashtype = "md5a";
			else if(!strncmp($hash, '$2a$', 4) && substr($hash, 6, 1) == '$')
				$hashtype = "bf";
			else
				continue;

			if(!isset($hashstats[$hashtype]))
				$hashstats[$hashtype] = 0;
			$hashstats[$hashtype] += 1;

			$q = "INSERT INTO hashes SET job_id='". $m->escape_string($job_id) ."', hash='". $m->escape_string($hash) ."'";
			if(@$m->query($q) === FALSE) {
				log_event("Failed to insert hash in hashtable: ". $m->error);
				$_SESSION["error"] = "Sorry, an internal database error occured while importing hashes. The job is not complete!";
				break;
			}


			// Add certain hashes to the onlinerainbowtables table
			if(!strcasecmp($hashtype, "raw-MD5")) {
				$q = "INSERT INTO onlinerainbowtables SET hash_id='". $m->escape_string($m->insert_id) ."'";
				@$m->query($q);
			}
			else if(!strcasecmp($hashtype, "mysql-fast") || !strcasecmp($hashtype, "MySQL")) {
				$q = "INSERT INTO onlinerainbowtables SET hash_id='". $m->escape_string($m->insert_id) ."'";
				@$m->query($q);
			}

			$num_entries++;
		}

		fclose($fd);


		// Remove job if no hashes were found
		if($num_entries == 0) {
			$q = "DELETE FROM jobs WHERE id='". $m->escape_string($job_id) ."'";
			$m->query($q);

			$_SESSION["error"] = "No hashes were found in uploaded file. The job was NOT created.\n";
			break;
		}


		$jobflags = 0;
		$jobflags |= JOB_FLAG_ACTIVE;
		if($attack_modes[$_POST["mode"]]["mode"] == "incremental")
			$jobflags |= JOB_FLAG_INCREMENTAL;
		else if ($attack_modes[$_POST["mode"]]["mode"] == "wordlist")
			$jobflags |= JOB_FLAG_WORDLIST;



		// Update jobs table to reflect hashtype, number of imported hashes and job flags
		$q = "UPDATE jobs SET hashtype='". $m->escape_string($hashtype) ."', summary_numhashes=$num_entries, jobflags=$jobflags WHERE id='". $m->escape_string($job_id) ."'";
		@$m->query($q);


		// Share job with groups the user have access to based on the selection in the form (groups[])
		foreach(user_get_groups($_SESSION["u"]->id) as $g) {
			// Only add selected groups
			if(!in_array($g->id, $post_groups)) {

				// Skip non-selected groups if it wasn't the public group (id 1)
				if($g->id != 1)
					continue;

				// Only skip the public group if the user has a premium account
				if(($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == USER_FLAG_PREMIUM)
					continue;
			}

			$q = "INSERT INTO group_jobs SET group_id='". $m->escape_string($g->id) ."', job_id='". $m->escape_string($job_id) ."'";
			@$m->query($q);
		}

		
		$_SESSION["info"] = "Job created. $num_entries $hashtype hashes imported.";

		$logstr = "Job '". $_POST["jobname"] ."' created. ";
		foreach($hashstats as $h => $n) 
			$logstr .= "$n hashes of type $h.";
		log_event($logstr);


		header("Location: $root_url"."jobs?id=$job_id");

	} while(0);
?>
