<?php

/*
 * Fetch work to do!
 *
 */
 
	require("../config.php");
	require("../init.php");
	require("lib.php");

	header("Content-Type: text/plain");


	// Authenticate node
	if(!isset($_COOKIE["node"]))
		tjohn_error(TJOHN_ERROR_AUTH, "Authentication cookie not set");

	$node = tjohn_auth_node($_COOKIE["node"]);

	// Keep track of the node's software version
	tjohn_node_set_useragent($node->id);



	// Fetch jobs belonging to this node's owner
	$q = "SELECT jobs.id, jobs.owner_user_id FROM jobs WHERE jobflags&". (JOB_FLAG_DONE|JOB_FLAG_ACTIVE) ."=". (JOB_FLAG_ACTIVE) ." AND owner_user_id=$node->user_id AND FIND_IN_SET(jobs.hashtype, '". $m->escape_string($node->ciphers) ."')";


	// If this node's owner is member of groups, use a different
	// query to include jobs from these groups too
	$groups = tjohn_group_get_memberships($node->user_id);
	if(count($groups)) {
		$q = "SELECT jobs.id, jobs.owner_user_id FROM jobs WHERE jobflags&". (JOB_FLAG_DONE|JOB_FLAG_ACTIVE) ."=". (JOB_FLAG_ACTIVE) ." AND (owner_user_id=$node->user_id OR id IN(SELECT job_id FROM group_jobs WHERE group_id IN('". implode("', '", $groups) ."'))) AND FIND_IN_SET(jobs.hashtype, '". $m->escape_string($node->ciphers) ."')";
		
	}



	// Fetch private and shared jobs available to work on
	$job_id_private = array();
	$job_id_shared = array();

	if(($r = @$m->query($q)) === FALSE) {
		log_event("Failed to fetch available jobs for this node's (id: $node->id)  owner: ". $m->error);
		tjohn_error(TJOHN_ERROR_DB, "Failed to fetch available jobs for this node's (id: $node->id) owner: ". $m->error);
	}

	while($row = $r->fetch_object()) {
		if($row->owner_user_id == $node->user_id)
			$job_id_private[] = $row->id;
		else
			$job_id_shared[] = $row->id;
	}

	$r->close();



	// XXX - Do job weighting
	// XXX - Cheat and pick a random job of those
	// XXX - Handle no jobs (pick from public to not waste CPU time? :)
	$jobs = array_merge($job_id_private, $job_id_shared);
	if(count($jobs) == 0) {
		tjohn_error(TJOHN_ERROR_RESTART, "You've run out of jobs to work on");
	}


	// Sort jobs on id (MySQL optimization for the queryies below)
	sort($jobs, SORT_NUMERIC);

	// Fill the array $job_list with arrays of up to $num_jobs_per_entry job ids
	$job_list = array();
	$num_jobs = count($jobs);
	$num_jobs_per_entry = 100;
	for($i = 0; $i < $num_jobs; $i++) {
		$jl_index = floor($i / $num_jobs_per_entry);
		if(!isset($job_list[$jl_index]))
			$job_list[$jl_index] = array();

		$job_list[$jl_index][] = $jobs[$i];
	}

	// Find the date of the last created, and active, job for this node's accessible jobs
	$last_created_job_date = "0000-00-00 00:00:00";
	// Default: Only consider the last 24 hours
	$last_created_job_date = date("Y-m-d H:i:s", time() -  86400);
	foreach($job_list as $job_id_list) {
		$q = "SELECT MAX(dt_created) dt_created FROM jobs WHERE id IN (". implode(",", $job_id_list) .") AND jobflags & ". (JOB_FLAG_ACTIVE|JOB_FLAG_DONE) ." = ". (JOB_FLAG_ACTIVE) ."";
		if(($r = @$m->query($q)) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, "Failed to find last created, and active, job: ". $m->error);

		$row = $r->fetch_object();
		$r->close();

		if($row->dt_created == NULL)
			continue;
		
		if($row->dt_created > $last_created_job_date)
			$last_created_job_date = $row->dt_created;
	}

	

	// Build a list of how many times each job has been requested since $last_created_job_date
	// Index is number of requests, data a list of those job ids
	$job_requests = array();
	$job_requests[0] = array();
	foreach($job_list as $job_id_list) {
		$q = "SELECT job_id, COUNT(*) num_requests FROM jobrequests WHERE job_id IN (". implode(",", $job_id_list) .") AND dt_requested > '". $m->escape_string($last_created_job_date) ."' GROUP BY job_id";

		if(($r = @$m->query($q)) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, "Failed to query jobrequests table: ". $m->error);


		// Keep track of those job_id's that weren't returned in the above query
		$job_ids_seen = array();
		while($row = $r->fetch_object()) {
			$job_ids_seen[] = $row->job_id;
			if(!isset($job_requests[$row->num_requests]))
				$job_requests[$row->num_requests] = array();

			$job_requests[$row->num_requests][] = $row->job_id;
		}
		$r->close();


		// Add jobs not seen to the zero-requests entry
		foreach($job_id_list as $job_id)
			if(!in_array($job_id, $job_ids_seen))
				$job_requests[0][] = $job_id;
	}

	// Sort the list on the key, i.e, the number of job requests
	ksort($job_requests, SORT_NUMERIC);

	// Extract a list of jobs with the least requests
	do {
		$jobs = array_shift($job_requests);
	} while(count($jobs) == 0 && count($job_requests));


	// Make sure we at least found one job
	$num_found = count($jobs);
	if($num_found == 0)
		tjohn_error(TJOHN_ERROR_RESTART, "No available jobs. Try again!");

	// Pick one of the jobs (randomly, if multiple choice)
	if($num_found == 1)
		$job_id = $jobs[0];
	else
		$job_id = $jobs[mt_rand(0, count($jobs) - 1)];



	// Now extract the details for this particular job
	$q = "SELECT * FROM jobs WHERE id='". $m->escape_string($job_id) ."'";
	if(($r = $m->query($q)) === FALSE)
		tjohn_error(TJOHN_ERROR_DB, "Failed to fetch job information for job with id '". $job_id ."'");

	$job = $r->fetch_object();
	$r->close();



	// Log that this node was assigned this job
	$q = "INSERT INTO jobrequests SET job_id='". $m->escape_string($job->id) ."', node_id='". $m->escape_string($node->id) ."'";
	@$m->query($q);



	// Reflect the node's selected job in the nodes table
	$q = "UPDATE nodes SET current_job_id='". $m->escape_string($job->id) ."', dt_lastactive=NOW() WHERE id='". $m->escape_string($node->id) ."'";
	if(@$m->query($q) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed to reflect selected job in node table: $m->error");


	// Update dt_lastactive on the job
	$q = "UPDATE jobs SET dt_lastactive = NOW() WHERE id='". $m->escape_string($job->id) ."'";
	if(@$m->query($q) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed to update job's last active timestamp: $m->error");


	
	// Tell the client what mode to use (incremental or wordlist)
	// See ../config.php for a list of attack modes (i.e: "incremental all" or "wordlist quick")

	
	$work = TJOHN_SUCCESS ." ". $attack_modes[$job->attack_mode]["mode"] ." ". $attack_modes[$job->attack_mode]["options"] ." $job->id $job->jobname\n";
	log_event(trim($work) . " assigned to node $node->id ($node->nodename) at IP ". substr(md5($_SERVER["REMOTE_ADDR"], 0, 16)));


	// Output the job parameters to the client
	echo $work;



	// Feed the client the hashlist
	$q = "SELECT hash FROM hashes WHERE job_id='". $m->escape_string($job->id) ."' AND plaintext IS NULL";
	if(($r = @$m->query($q)) !== FALSE) {
		$i = 0;
		while($row = $r->fetch_object()) {
			$i++;
			echo "$i:$row->hash\n";
		}

		$r->close();
	}

?>
