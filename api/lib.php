<?php

	define("TJOHN_SUCCESS", 100);
	define("TJOHN_ERROR_RESTART", 150);
	define("TJOHN_ERROR_AUTH", 200);
	define("TJOHN_ERROR_DB", 300);
	define("TJOHN_ERROR_UPGRADE", 400);
	define("TJOHN_ERROR_MISC", 500);
	function tjohn_error($errortype, $errormessage) {
		die("$errortype $errormessage\n");
	}	



        function log_event($log) {
                global $m;

                $user_id = -1;
                if(isset($_SESSION["u"]))
                        $user_id = $_SESSION["u"]->id;

                $ip = substr(md5($_SERVER["REMOTE_ADDR"]), 0, 16);

                $req_uri = $_SERVER["REQUEST_URI"];

                $useragent = "";
                foreach(apache_request_headers() as $h => $v) {
                        if(strcasecmp($h, "User-Agent") == 0)
                                $useragent = $v;
                }

                $q = "INSERT INTO eventlog SET user_id='". $m->escape_string($user_id) ."', ip='". $m->escape_string($ip) ."', useragent='". $m->escape_string($useragent) ."', req_uri='". $m->escape_string($req_uri) ."', log='". $m->escape_string($log) ."'";
                @$m->query($q);
        }

	/*
	 * Log a failed user authentication attempt
	 * XXX - Prevent bruteforces
	 *
	 */
	function tjohn_auth_user_log_failed_attempt($username, $password) {
		global $m;

		$q = "INSERT INTO autherrors SET user='". $m->escape_string($username) ."', pass='". $m->escape_string($password) ."'";
		if(@$m->query($q) === FALSE) {
			return FALSE;
		}

		return TRUE;
	}


	/*
	 * Authenticate a user.
	 * This function always returns the users's id
	 *
	 */
	function tjohn_auth_user($username, $password) {
		global $m;


		$q = "SELECT * FROM users WHERE username='". $m->escape_string($username) ."' AND password='". $m->escape_string(md5($password)) ."'";
		$q = "SELECT * FROM users WHERE username='". $m->escape_string($username) ."' AND password='". $m->escape_string($password) ."'";
		if(($r = @$m->query($q)) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, "webapi_auth_user(): $m->error");

		if(($row = $r->fetch_object()) === NULL) {
			if(tjohn_auth_user_log_failed_attempt($username, $password) === FALSE)
				tjohn_error(TJOHN_ERROR_DB, $m->error);

			tjohn_error(TJOHN_ERROR_AUTH, "Invalid username and/or password");
		}

		$r->close();


		$q = "UPDATE users SET dt_lastlogin=NOW() WHERE id=$row->id";
		if(@$m->query($q) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, "webapi_auth_user(): $m->error");

		return $row;
	}


	/*
	 * Authenticate a node
	 *
	 */
	function tjohn_auth_node($authcookie) {
		global $m;


		$q = "SELECT * FROM nodes WHERE authcookie='". $m->escape_string($authcookie) ."'";
		if(($r = $m->query($q)) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, $m->error);

		if(($row = $r->fetch_object()) === FALSE) {
			tjohn_error(TJOHN_ERROR_AUTH, "Unknown node");
		}

		$r->close();


		// XXX - Just stupid?
		$q = "UPDATE nodes SET dt_lastactive=NOW() WHERE id=$row->id";
		if(@$m->query($q) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, $m->error);


		return $row;
	}


	/*
	 * Get group id id member ships
	 *
	 */
	function tjohn_group_get_memberships($user_id) {
		global $m;

		$ret = array();

		$q = "SELECT group_id FROM group_members WHERE user_id='". $m->escape_string($user_id) ."'";
		if(($r = $m->query($q)) === FALSE)
			tjohn_error(TJOHN_ERROR_DB, $m->error);

		while($row = $r->fetch_object())
			$ret[] = $row->group_id;
		$r->close();

		return $ret;
	}


	function tjohn_node_set_useragent($node_id) {
		global $m;

		$h = apache_request_headers();
		if(!isset($h["User-Agent"]))
			return;

		$q = "UPDATE nodes SET useragent='". $m->escape_string($h["User-Agent"]) ."' WHERE id='". $m->escape_string($node_id) ."'";
		$m->query($q);
	}



?>
