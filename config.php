<?php

	// Database settings
	$db_host = "localhost";
	$db_user = "foo";
	$db_pass = "bar";
	$db_name = "distributedcracking";


	// Root URL of installation. Include the trailing slash.
	// Used in redirects by the web GUI
	// Note: This needs to be configured in the  client source
	//       code (webapi.h) aswell
	$root_url = "https://example.net/";


	// Temporary directory. Include the trailing slash.
	// Must be writable by the user Apache (or PHP) is running as.
	$temp_dir = "temp/";


	// User flags
	define("USER_FLAG_PREMIUM",	0x01);


	// Job flags
	define("JOB_FLAG_INCREMENTAL",		0x01);	// Job has incremental cracking
	define("JOB_FLAG_INCREMENTAL_DONE",	0x02);	// No more packets to generate
	define("JOB_FLAG_WORDLIST",		0x04);	// Job has wordlist cracking
	define("JOB_FLAG_WORDLIST_DONE",	0x04);	// No more packets to generate
	define("JOB_FLAG_DELETED",		0x20);	// Job has been deleted
	define("JOB_FLAG_DONE",			0x40);	// All hashes cracked
	define("JOB_FLAG_ACTIVE",		0x80);	// An active job


	// Premium account price
	$premium_account_price 		= "1.99";


	// Invite only?
	$invite_only			= FALSE;


	// List of known attack modes
	$attack_modes = array(
		// The key is used in jobs.attach_mode
		10 => array(
			// Shown on the Jobs web page
			"text"		=> "Incremental: 1-8 characters (all characters)",

			// What's sent to the client
			"mode"		=> "incremental",
			"options"	=> "all"
		),
		20 => array(
			"text"		=> "Incremental: 1-8 characters (alphanumeric only)",
			"mode"		=> "incremental",
			"options"	=> "alnum"
		),
		30 => array(
			"text"		=> "Incremental: 1-8 characters (alpha only)",
			"mode"		=> "incremental",
			"options"	=> "alpha"
		),
		40 => array(
			"text"		=> "Incremental: 1-8 characters (digits only)",
			"mode"		=> "incremental",
			"options"	=> "digits"
		),
/*
		50 => array(
			"text"		=> "Wordlist (with rules)",
			"mode"		=> "wordlist",
			"options"	=> "rules"
		),
		60 => array(
			"text"		=> "Wordlist (quick, no rules)",
			"mode"		=> "wordlist",
			"options"	=> "quick"
		),
*/
	);


	// Web GUI configuration
	// =====================

	// Contact default mail from and mail to
	// Used by a form in the web GUI
	$contact_mail_from	= "contact <contact@EXAMPLE.NET>";
	$contact_mail_to	= "contact@EXAMPLE.NET";


	$paypal_mail_to		= "paypal@EXAMPLE.NET";

	// Maximum number of jobs to display on the user's home page
	$home_max_number_of_jobs	= 4;

	// Maximum number of nodes to display on the user's home page
	$home_max_number_of_nodes	= 20;





	// Backend configuration
	// =====================

	// Path to binary that generates packets for
	// Table columns used: jobs.incremental_params_next, packets.incremental_params
	$incremental_path_generate	= "/opt/distributedcracking.net/backend/incremental/generatepacket";


	// NOTE: These two variables should be setup that we never reach $incremental_min_available_packets
	//       between two consecutive runs of the script that generate packets.

	// Minimum numbers of available packets a job can have before new are created
	$incremental_min_available_packets = 15;

	// Number of packets we seek to have free when the current number
	// of free packets become equal or below $incremental_min_available_packets
	$incremental_num_free_packets_required = 30;


	// Maximum number of rounds to generate
	$incremental_max_num_rounds	= 10 * 1000 * 1000 * 1000;


	// This is based on the number of crypts/second on Xeon 3.0GHz CPU
	// which we consider a standard CPU.
	// The resulting packet size is calculated from this value
	// multiplicated with the number of hashes left to crack
	// and the wanted number of seconds each packet should take
	// to process
	$incremental_packet_size	= array(
		// Intel(R) Xeon(TM) CPU 3.00GHz 2MB 15:4:3 (familiy:model:stepping)

		// Benchmarking: Traditional DES [128/128 BS SSE2]... DONE
		// Many salts:     675424 c/s real, 675424 c/s virtual
		// Only one salt:  595488 c/s real, 595488 c/s virtual
		'des'		=> 675000,

		// Benchmarking: FreeBSD MD5 [32/32]... DONE
		// Raw:    5749 c/s real, 5749 c/s virtual
		// Realworld, 81 hashes -> 5100
		'md5'		=> 5100,

		// Benchmarking: Raw MD5 [raw-md5]... DONE
		// Raw:    2478K c/s real, 2478K c/s virtual
		// Realworld, 847 hashes -> 18.5M
		'raw-md5'	=> 18500000,

		// Benchmarking: Raw MD5 [raw-md5]... DONE
		// Raw:    2478K c/s real, 2478K c/s virtual
		// Realworld, 847 hashes -> 18.5M
		'raw-sha1'	=> 290000000,


		// Benchmarking: MYSQL_fast [mysql-fast]... DONE
		// Raw:    9700K c/s real, 9700K c/s virtual
		// Realworld, 665 hashes -> 68.1M
		'mysql-fast'	=> 68100000,


		'mysql'		=>  7400000,

		'afs'		=> 325000,
		'bf'		=> 250,
		'bfegg'		=> 3200,
		'bsdi'		=> 20000,
		'krb4'		=> 1500000,
		'krb5'		=> 1000000,
		'lm'		=> 3500000,
		'nt'		=> 300000,
		'skey'		=> 500000,
	);

	// The number of seconds we would like a standard CPU to spend
	// cracking a packet
	$incremental_avg_crack_time	= 7200;

	// Number of seconds before a packet is assigned to another node
	// Make sure it's set high enough so that slow nodes don't waste
	// their time working on a packet which is later reassigned to a
	// faster node
	$incremental_packet_timeout	= $incremental_packet_timeout * 4;


	// For the hash verification (cracked_hashes)
	// Directory name where the john program is installed and where john.pot is located
	// Must contain a trailing slash!
	$hash_verification_jtr_root	= "/opt/distributedcracking.net/backend/hash-verification/";

?>
