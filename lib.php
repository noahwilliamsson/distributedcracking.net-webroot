<?php

	function web_validate_session() {

		if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== TRUE) {
			return FALSE;
		}

		return TRUE;
	}

	function web_validate_session_and_redirect() {
		global $page_title, $page_file, $page_error;

		if(web_validate_session())
			return TRUE;

		$page_title = "Not logged in";
		$page_file = "not-loggedin.php";
		$page_error = "You're not logged in. Your session might have timed out if you were previously logged in.";

		if(isset($_GET["page"]))
			$_SESSION["redirect_page"] = $_GET["page"];

		header("Location: $root_url"."not-logged-in");
		
		return FALSE;
	}

	function log_event($log) {
		global $m;
		
		$user_id = -1;
		if(isset($_SESSION["u"]))
			$user_id = $_SESSION["u"]->id;

		$ip = substr(md5($_SERVER["REMOTE_ADDR"], 0, 16));

		$req_uri = $_SERVER["REQUEST_URI"];

		$useragent = "";
		foreach(apache_request_headers() as $h => $v) {
			if(strcasecmp($h, "User-Agent") == 0)
				$useragent = $v;
		}

		$q = "INSERT INTO eventlog SET user_id='". $m->escape_string($user_id) ."', ip='". $m->escape_string($ip) ."', useragent='". $m->escape_string($useragent) ."', req_uri='". $m->escape_string($req_uri) ."', log='". $m->escape_string($log) ."'";
		@$m->query($q);
	}


	function session_cache_save() {
		global $m;

		$q = "UPDATE sessioncache SET session='". $m->escape_string(serialize($_SESSION["c"])) ."' WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
		@$m->query($q);
	}


	function date_friendly($time) {
		if($time == NULL || $time == 0 || $time == "0000-00-00 00:00:00")
			return "Never";


		$now = time();
		$t = strtotime($time);
		$diff = $now - $t;

		$ret = "";
		if($diff < 120)
			$ret = "a moment ago";
		else if($diff < 7200)
			$ret = round($diff / 60) ." minutes ago";
		else if($diff < 86400) 
			$ret = round($diff / 3600) ." hours ago";
		else if($diff < 7*86400) 
			$ret = floor($diff / 86400) ." days and ". round(($diff % 86400) / 3600) ." hours ago";
		else if(date("Y") == date("Y", $t))
			$ret = strftime("%a %d %b", $t);
		else
			$ret = strftime("%a %d %b, %Y", $t);

		return $ret;
	}


	function time_friendly($t, $short_format = FALSE) {
		if($t == NULL)
			return "none";



		$timespent = "";
		$did_month = FALSE;
		if($t > 31 * 86400) {
			$temp = floor($t / (31 * 86400));
			$t -= 31 * 86400 * $temp;
			if(!empty($timespent)) $timespent .= ", ";
			if($short_format)
				$timespent .= sprintf("%dmon ", $temp);
			else
				$timespent .= sprintf("%d month%s", $temp, $temp != 1? "s": "");

			$did_month = TRUE;
		}
		if($t > 7 * 86400) {
			$temp = floor($t / (7 * 86400));
			$t -= 7 * 86400 * $temp;
			if(!empty($timespent)) $timespent .= ", ";
			if($short_format)
				$timespent .= sprintf("%dw ", $temp);
			else
				$timespent .= sprintf("%d week%s", $temp, $temp != 1? "s": "");
		}
		if($t > 86400) {
			$temp = floor($t / 86400);
			$t -= 86400 * $temp;
			if(!empty($timespent)) $timespent .= ", ";
			if($short_format)
				$timespent .= sprintf("%dd", $temp);
			else
				$timespent .= sprintf("%d day%s", $temp, $temp != 1? "s": "");
		}
		if($t > 3600) {
			$temp = floor($t / 3600);
			$t -= 3600 * $temp;
			if(!empty($timespent)) $timespent .= ", ";
			if($short_format)
				$timespent .= sprintf("%dh", $temp);
			else
				$timespent .= sprintf("%d hour%s", $temp, $temp != 1? "s": "");
		}
		if($t > 60 && !$did_month) {
			$temp = floor($t / 60);
			$t -= 60 * $temp;
			if(!empty($timespent)) $timespent .= ", ";
			if($short_format)
				$timespent .= sprintf("%dm", $temp);
			else
				$timespent .= sprintf("%d minute%s", $temp, $temp != 1? "s": "");
		}

		return $timespent;
	}


	function cpu_short($cpuinfo) {	
		$cpuinfo = str_replace("Intel(R) Core(TM)2 Duo CPU", "Intel Core2 Duo", $cpuinfo);
		$cpuinfo = str_replace("Intel(R) Core(TM)2 CPU", "Intel Core 2", $cpuinfo);
		$cpuinfo = str_replace("Intel(R) Xeon(R) CPU", "Intel Xeon", $cpuinfo);
		$cpuinfo = str_replace("Intel(R) Xeon(TM) CPU", "Intel Xeon", $cpuinfo);
		$cpuinfo = str_replace("Dual-Core AMD Opteron(tm) Processor", "Dual-Core AMD Opteron", $cpuinfo);
		$cpuinfo = str_replace("AMD Athlon(tm) 64 X2 Dual Core Processor", "AMD Athlon 64 X2", $cpuinfo);
		$cpuinfo = str_replace("Mobile AMD Athlon 64 Processor", "AMD Mobile Athlon 64", $cpuinfo);
		$cpuinfo = str_replace("Intel(R) Pentium(R)", "Intel Pentium", $cpuinfo);
		$cpuinfo = str_replace("Intel Pentium M processor", "Intel Pentium M", $cpuinfo);
		return $cpuinfo;
	}


	function user_get_groups($user_id) {
		global $m;

		$ret = array();

		$q = "SELECT groups.* FROM group_members LEFT JOIN groups ON group_id=groups.id WHERE user_id='". $m->escape_string($user_id) ."' ORDER BY groupname";
		if(($r = @$m->query($q)) !== FALSE) {
			while($row = $r->fetch_object())
				$ret[] = $row;

			$r->close();
		}

		return $ret;
	}	

	function user_set_session_groups($user_id) {
		global $m;

		$_SESSION["groups"] = array();

		$q = "SELECT groups.*, group_admin FROM group_members LEFT JOIN groups ON group_id=groups.id WHERE user_id='". $m->escape_string($user_id) ."' ORDER BY groupname";
		if(($r = @$m->query($q)) === FALSE) {
			log_event("user_set_session_groups($user_id): Database error: $m->error. SQL: $q");
			return FALSE;
		}


		while($row = $r->fetch_object())
			$_SESSION["groups"][$row->id] = array("group" => $row->groupname, "admin" => $row->group_admin, "dt_created" => $row->dt_created,  "invite_code" => $row->invite_code);

		$r->close();

		return TRUE;
	}

?>
