<?php

/*
 * Adds cracked hashes to a queue for later verification
 *
 */
 
	require("../config.php");
	require("../init.php");
	require("lib.php");


	if(!isset($_COOKIE["node"]))
		tjohn_error(TJOHN_ERROR_AUTH, "Authentication cookie not set");

	$node = tjohn_auth_node($_COOKIE["node"]);


	if(!isset($_POST["pot"]) || !isset($_POST["j"]))
		tjohn_error(TJOHN_ERROR_MISC, "Parameters pot[] or j not set!");


	$errors = array();
	foreach($_POST["pot"] as $potline) {
		$temp = explode(":", $potline, 2);
		$hash = $temp[0];
		$plaintext = $temp[1];

		$m->set_charset("latin1");
		$q = "INSERT INTO cracked_hashes SET node_id=$node->id, job_id='". $m->escape_string($_POST["j"]) ."', hash='". $m->escape_string($hash) ."', plaintext='". $m->escape_string($plaintext) ."'";
		if(@$m->query($q) === FALSE)
			$errors[] = "Failed to add hash $hash with plaintext $plaintext: ". $m->error;
	}

	if(count($errors))
		tjohn_error(TJOHN_ERROR_DB, count($errors) ." errors encountered:\n". implode("\n", $errors));

	tjohn_error(TJOHN_SUCCESS, "");
?>
