<?php
	require("config.php");

	foreach($_SESSION as $k => $v) 
		unset($_SESSION[$k]);

	header("Location: $root_url");
?>
