<?php

/*
 * Register a node with the system
 *
 * The node sends the following parameters
 *	u		->	The name of an existing user
 *	p		->	The password for the supplied username
 *	ciphers		->	A list of supported ciphers
 *	ci		->	CPU information
 *
 * 1. Authenticate as an existing user
 * 2. Create a new node entry, saving the owner of the node and
 *    supported ciphers
 *
 * The node will receive an authentication cookie (md5 hash) 
 * as a proof of its identity.
 *
 * The node is the considered to be logged in.
 *
 */
 
	require("../config.php");
	require("../init.php");
	require("lib.php");


	/*
	 * Make sure necessary parameters are present
	 */
	if(!isset($_POST["u"]) || !isset($_POST["p"]) || !isset($_POST["ciphers"]) || !isset($_POST["cpuinfo"]))
		tjohn_error(TJOHN_ERROR_MISC, "Invalid parameters");


	/*
	 * Authenticate the supplied username/password
	 *
	 */
	$user = tjohn_auth_user($_POST["u"], $_POST["p"]);


	/*
	 * The user is authenticated. 
	 * Create an authcookie and register the node
	 *
	 */
	$authcookie = md5(md5($_POST["u"]) . date("YmdHiS") . md5($_POST["p"]) . (string)mt_rand());
	

	$q = "INSERT INTO nodes SET ";
	$q .= "user_id=$user->id, ";
	$q .= "ciphers='". $m->escape_string($_POST["ciphers"]) ."', ";
	$q .= "cpuinfo='". $m->escape_string($_POST["cpuinfo"]) ."', ";
	$q .= "authcookie='". $m->escape_string($authcookie) ."'";
	if(@$m->query($q) === FALSE)
		tjohn_error(TJOHN_ERROR_DB, $m->error);


        // Keep track of the node's software version
        tjohn_node_set_useragent($m->insert_id);

	log_event("Node $m->insert_id registered by user $user->id ($user->username)");

	setcookie("node", $authcookie);
	echo TJOHN_SUCCESS ." $authcookie";
?>
