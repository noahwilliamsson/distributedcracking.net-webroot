<?php

/*
 * Login a node with an auth cookie (the "a" parameter)
 * Sends a Set-Cookie header if successful
 *
 */
 
	require("../config.php");
	require("../init.php");
	require("lib.php");


	if(!isset($_POST["a"]))
		tjohn_error(TJOHN_ERROR_MISC, "Invalid parameters");


	$node = tjohn_auth_node($_POST["a"]);

        // Keep track of the node's software version
        tjohn_node_set_useragent($node->id);

	setcookie("node", $node->authcookie);
	echo TJOHN_SUCCESS ." Logged in as node $node->id";

	log_event("Node $node->id ($node->nodename) logged in");
?>
