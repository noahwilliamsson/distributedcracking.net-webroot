<?php
	/*
	 * Load settings (database config, root URL, ..) 
	 *
	 */
	require("config.php");

	/*
	 * Init MySQL connection
	 *
	 */
	require("init.php");

	/*
	 * Helper functions
	 *
	 */
	require("lib.php");



	/*
	 * Start session management
	 *
	 */
	session_start();



	/*
	 * Check if the user were originally destinated for another page
	 * and redirect accordingly 
	 *
	 */
	if(web_validate_session() && isset($_SESSION["redirect_page"])) {
		header("Location: $root_url". $_SESSION["redirect_page"]);
		unset($_SESSION["redirect_page"]);
		die;
	}



	/*
	 * Take care of the framework
	 *
	 */
	$temp_page = "";
	if(isset($_GET["page"]))
		$temp_page = $_GET["page"];


	$page_title	= "";
	$page_file	= "";
	$page_error	= "";
	switch($temp_page) {
		/*
		 * Show about page
		 *
		 */
		case "about":
			$page_title = "About us";
			$page_file = "about.php";
			break;


		/*
		 * Display contact page or send contact info
		 *
		 */
		case "contact":
			$page_title = "Contact us!";
			$page_file = "contact.php";
			if(count($_POST)) {
				require("post-contact.php");
			}
			break;

		
		/*
		 * Show FAQ
		 *
		 */
		case "frequently-asked-questions":
			$page_title = "Frequently asked questions";
			$page_file = "frequently-asked-questions.php";
			break;


		/*
		 * Display hash info page
		 *
		 */
		case "hashes":
			$page_title = "Information about hashes";
			$page_file = "hashes.php";
			break;




		
		/*
		 * Display create account page or create account
		 *
		 */
		case "create":
			$page_title = "Create an account, quick and easy";
			$page_file = "create.php";
			if(count($_POST)) {
				require("post-create.php");
			}
			break;

		
		/*
		 * Display login page or do login
		 *
		 */
		case "login":
			$page_title = "Login";
			$page_file = "login.php";
			if(count($_POST)) {
				require("post-login.php");
			}
			break;

		/*
		 * Display donate page
		 *
		 */
		case "donate":
			$page_title = "Donate to help us improve our services";
			$page_file = "donate.php";
			break;

		
		/*
		 * Log out user
		 *
		 */
		case "logout":
			require("logout.php");
			break;


		/*
		 * In case the session timed out, display an error page
		 * and let the user log in again
		 *
		 */
		case "not-logged-in":
			$page_file = "not-loggedin.php";
			$page_title = "Not logged in";
			break;



		// ===== The following pages are only accessible to logged in users ====


		/*
		 * Display intro page
		 *
		 */
		case "intro":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			$page_file = "intro.php";
			$page_title = "Introduction to distributed cracking";
			break;

		/*
		 * Display team information 
		 *
		 */
		case "teams":
		case "groups":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			$page_file = "teams.php";
			$page_title = "Manage team memberships";

			if(count($_POST)) {
				require("post-teams.php");
			}
			else if(isset($_GET["remove"])) {
				require("post-teams-edit.php");
			}

			if(isset($_GET["id"])) {
				$page_file = "teams-edit.php";
				$page_title = "Create or edit teams";
			}
			break;

		/*
		 * Display download page
		 *
		 */
		case "download":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			$page_file = "download.php";
			$page_title = "Download client software";
			break;

		/*
		 * Display jobs management page
		 *
		 */
		case "jobs":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			if(count($_POST)) {
				require("post-jobs.php");
			}

			$page_file = "jobs.php";
			$page_title = "Manage jobs";
			if(isset($_GET["id"]) && intval($_GET["id"])) {
				$page_file = "jobs-edit.php";
				$page_title = "Change settings for job";
			}
			break;

		/*
		 * Display jobs management page
		 *
		 */
		case "jobs-edit":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			if(count($_POST)) {
				require("post-jobs-edit.php");
			}

			header("Location: $root_url"."jobs?id=". intval($_GET["id"]));
			break;

		/*
		 * Display nodes management page
		 *
		 */
		case "nodes":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			if(count($_POST)) {
				require("post-nodes.php");
			}

			$page_file = "nodes.php";
			$page_title = "Manage nodes";
			if(isset($_GET["id"]) && intval($_GET["id"])) {
				$page_file = "nodes-edit.php";
				$page_title = "Change settings for node";
			}
			break;

		/*
		 * Export hashes
		 *
		 */
		case "export-hashes":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			if(count($_POST)) {
				require("export-hashes.php");
			}

			$page_file = "jobs.php";
			$page_title = "Manage jobs";
			if(isset($_GET["id"]) && intval($_GET["id"])) {
				$page_file = "jobs-edit.php";
				$page_title = "Change settings for job";
			}
			break;

		/*
		 * Display account page
		 *
		 */
		case "account":
		case "settings":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			$page_file = "account.php";
			$page_title = "Manage account";
			break;

		/*
		 * Display upgraded page - the return page from Paypal
		 *
		 */
		case "paypal":
			if(web_validate_session_and_redirect() === FALSE)
				die;

			$page_file = "paypal.php";
			$page_title = "Return from PayPal";
			break;

		/*
		 * Show start page or, for logged in users, their default page
		 *
		 */
		default:
			$page_file = "frontpage.php";
			$page_title = "Distributed online cracking that doesn't suck";

			if(web_validate_session()) {
				$page_file = "loggedin.php";
				$page_title = "Logged in - Home";
			}
			break;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="sv" xmlns="http://www.w3.org/1999/xhtml" xml:lang="sv">
<head>
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="Distributed, online cracking that doesn't suck"/>
<meta name="keywords" content="Distributed,cracking,online,md5,des,rawmd5,password,plaintext"/>
<style type="text/css">
body, html { 
	font-size: 16px;
	font-family: Helvetica;
	margin: 0;
	padding: 0;

	background-image: url(img/bluegrad_short.gif);
	background-position: 0px 0px;
	background-repeat: repeat-x;
	background-attachment: fixed;
}

h1, h2, h3, h4{
	margin-bottom: 4px;
}

p {
	margin-top: 3px;
	padding-top: 0;
}

input {
	margin-bottom: 10px;
	margin-left: 20px;
	margin-top: 4px;
	font-size: 16px;
}
label {
	font-weight: bold;
}


#header {
	height: 34px;
	width: 100%;
	background-image: url(img/grass_tile.gif);
	background-repeat: repeat-x;
}

#header h1 {
	margin: 0 auto;
	width: 800px;
	text-align: right;
	color: white;
}

#header a {
	color: white;
	text-decoration:underline
}

#wrapper {
	margin: 0 auto;
	padding-left: 5px;
	width: 810px;
}

#content {
	padding: 4px;
	width: 800px; 
	float: left;
}
/*
#ggl {
	margin-top: 50px;
	margin-left: 20px;
	//padding-left: 5px;
	//width: 120x;
	float: left;
	//border-left: 3px dashed green;
}
#ggl h4,
#ggl p {
	margin: 0;
}
*/

div.info {
	background: url(img/info.gif) no-repeat;
	padding-left: 52px;
	min-height: 48px;
}
div.note {
	background: black;
	background-image: url(img/note.gif);
	background-repeat: no-repeat;
	padding: 4px 4px 4px 32px;
	min-height: 34px;
	border: 1px solid #b2b7bc;
	color: white;
}


.sheet {
	border: 1px solid #b2b7bc;
	background: lightyellow;
	padding: 4px;
}

table.jobs,
table.nodes {
	padding: 4px;
	border: 1px solid #b2b7bc;
	background: lightyellow;
}

table.nodes th {
	text-align: left;
	width: 6em;
}
table.nodes tr {
	vertical-align: top;
}


table.jobs tr {
	height: 17px;
	text-align: left;
}

table td { padding: 1px 5px }
table.jobs tr td:first-child {
	padding-left: 12px;
	background: url(img/rarrow.gif) no-repeat;
}
#menu a { padding-top: 4px; }
#menu a.cur {
	padding-left: 12px;
	background: url(img/line2.gif) no-repeat;
}

#footer {
	clear: both;
	padding: 4px;
	height: 50px;
	border-top: 1px solid #b2b7bc;
}
#menu a,
#footer a {
	margin-right: 10px;
}


</style>
</head>
<body>
<div id="header">
		<h1><a href="<?= $root_url ?>">Distributed cracking (beta)</a></h1>
</div>

<!-- wrapper: {{{ -->
<div id="wrapper">
	<!-- content: {{{ -->
	<div id="content">
<?php
		if(isset($_SESSION["loggedin"]))
			require("loggedin-menu.php");

		require($page_file);
?>
	</div>
	<!-- content: }}} -->


	<!-- footer: {{{ -->
	<div id="footer">
		<p>
<?php
	if(!isset($_SESSION["u"])) {
?>
			<a href="login">Login</a>
<?php
	}
?>
			<a href="frequently-asked-questions">Frequently asked questions</a>
			<a href="contact">Contact</a>
			<a href="frequently-asked-questions#about">About</a>
			<a href="donate">Donate</a>
		</p>
	</div>
	<!-- footer: }}} -->


</div>
<!-- wrapper: }}} -->

</body>
</html>
