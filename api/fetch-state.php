<?php

/*
 * Fetch current state!
 *
 */
 
	require("../config.php");
	require("../init.php");
	require("lib.php");


	// Authenticate node
	if(!isset($_COOKIE["node"]))
		tjohn_error(TJOHN_ERROR_AUTH, "Authentication cookie not set");

	$node = tjohn_auth_node($_COOKIE["node"]);


	if(!isset($_GET["j"])) {
		log_event("Missing parameter");
		tjohn_error(TJOHN_ERROR_MISC, "Missing parameter");
	}


	if($node->current_job_id != $_GET["j"]) {
		log_event("Registered request for hashes doesn't match requested jobs. Two nodes sharing the same auth cookie, maybe?");
		tjohn_error(TJOHN_ERROR_MISC, "Registered request for hashes doesn't match requested jobs. Two nodes sharing the same auth cookie, maybe?");
	}



	// Pickup a packet to work on
	$q = "SELECT * FROM packets WHERE job_id='". $m->escape_string($node->current_job_id) ."' AND done=0 AND (acquired IS NULL OR acquired < DATE_SUB(NOW(), INTERVAL $incremental_packet_timeout SECOND)) ORDER BY acquired, id LIMIT 1";



	if(($r = @$m->query($q)) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed to fetch a packet for this node: ". $m->error);

	if(($row = $r->fetch_object()) === NULL)
		tjohn_error(TJOHN_ERROR_RESTART, "No available packets right now. Start over and pick a new job.");

	$r->close();


	// Take ownership of it by updating node_id and acquired
	$q = "UPDATE packets SET acquired=NOW(), node_id='". $m->escape_string($node->id) ."' WHERE id='". $m->escape_string($row->id) ."'";
	if(@$m->query($q) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed take ownership of packet: $m->error");


	// See ../config.php for a list of attack modes (i.e: "incremental all" or "wordlist quick")
	echo TJOHN_SUCCESS ." $row->id $row->incremental_rounds ". str_replace("\t", ",", $row->incremental_params);

?>
