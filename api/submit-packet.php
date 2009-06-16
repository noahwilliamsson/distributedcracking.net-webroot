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


	if(!isset($_GET["j"]) || !isset($_GET["p"]))
		tjohn_error(TJOHN_ERROR_MISC, "Missing parameters");


	if($node->current_job_id != $_GET["j"])
		tjohn_error(TJOHN_SUCCESS, "Registered request for hashes doesn't match submitted job. Two nodes sharing the same auth cookie, maybe?");




	$q = "SELECT * FROM packets WHERE id='". $m->escape_string($_GET["p"]) ."' AND job_id='". $m->escape_string($_GET["j"]) ."'";
	if(($r = @$m->query($q)) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed to identify submitted packet: ". $m->error);

	if(($row = $r->fetch_object()) === NULL)
		tjohn_error(TJOHN_SUCCESS, "Packet doesn't belong to you.");

	$r->close();



	$q = "UPDATE packets SET done=1, completed=NOW() WHERE id='". $m->escape_string($row->id) ."'";
	if(@$m->query($q) === FALSE) 
		tjohn_error(TJOHN_ERROR_DB, "Failed to finish packet: $m->error");


	// See ../config.php for a list of attack modes (i.e: "incremental all" or "wordlist quick")
	echo TJOHN_SUCCESS;
?>
