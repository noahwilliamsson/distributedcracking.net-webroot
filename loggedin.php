<?php

        // Print out session messages (info, errors)
        require("session-messages.php");

?>

		<h1>Home</h1>
		<p>
		Here's a brief overview of your account.
		</p>
		<hr />


		<h2>Your jobs</h2>
<?php
	$jobs = array();
	$jobs_found = 0;
	$q = "SELECT * FROM jobs WHERE owner_user_id='". $m->escape_string($_SESSION["u"]->id) ."' ORDER BY dt_lastactive DESC LIMIT ". ($home_max_number_of_jobs + 1);
	if(($r = @$m->query($q)) !== FALSE) {
		$jobs_found = $r->num_rows;
		while(($row = $r->fetch_object()) && count($jobs) < $home_max_number_of_jobs)
			$jobs[] = $row;

		$r->close();
	}

	if(count($jobs) == 0) {
?>
	<div class="info">
		<p>You haven't created any jobs yet. <a href="jobs#create">Create a new job</a> here.</p>
	</div>
<?php
	}
	else {
?>
	<table class="jobs">
		<tr>
			<th>Description</th>
			<th>Hash type</th>
			<th>Plaintexts</th>
		</tr>
<?php
		foreach($jobs as $row) {
?>
		<tr>
			<td><a href="jobs?id=<?= $row->id ?>" title="Change settings for this job"><?= htmlspecialchars($row->jobname) ?></td>
			<td><?= htmlspecialchars($row->hashtype) ?></td>
			<td align="right"><?= htmlspecialchars($row->summary_numcracked ." / ". $row->summary_numhashes) ?></td>
		</tr>
<?php
		}
?>
	</table>
	<p>
<?php
		if($jobs_found > $home_max_number_of_jobs) {
?>
	<a href="jobs">..additional jobs were found</a><br />
<?php
		} // more jobs available
	} // found jobs
?>
		Manage your jobs and those shared with team members on the <a href="jobs">jobs</a> page.</p>
		<hr />


		<h2>Your nodes</h2>
<?php
	$nodes = array();
	$nodes_found = 0;
	$q = "SELECT nodes.*, jobs.jobname FROM nodes LEFT JOIN jobs ON current_job_id=jobs.id WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."' ORDER BY nodes.dt_lastactive DESC LIMIT ". ($home_max_number_of_nodes + 1);
	if(($r = @$m->query($q)) !== FALSE) {
		$nodes_found = $r->num_rows;
		while(($row = $r->fetch_object()) && count($nodes) < $home_max_number_of_nodes)
			$nodes[] = $row;

		$r->close();
	}

	if(count($nodes) == 0) {
?>
	<div class="info">
		<p>You have currently not registered any nodes. <a href="download">Download</a> the client software and register using the username and password you use to login to this web page.</p>
	</div>
<?php
	}
	else {
		$node_counter = 0;
?>
	<table class="jobs">
		<tr>
			<th>Nickname</th>
			<th>CPU</th>
			<th>Last seen</th>
			<th>..working on</th>
		</tr>
<?php
		foreach($nodes as $row) {
			$node_counter++;

			if($row->nodename == NULL)
				$row->nodename = "<no name>";
			if($row->jobname == NULL)
				$row->jobname = "<nothing yet>";

?>
		<tr>
			<td><?= $node_counter ."." ?> <a href="nodes?id=<?= $row->id ?>" title="Change settings for this node"><?= htmlspecialchars($row->nodename) ?></td>
			<td><?= htmlspecialchars(cpu_short($row->cpuinfo)) ?></td>
			<td><?= htmlspecialchars(date_friendly($row->dt_lastactive)) ?></td>
			<td><?= htmlspecialchars($row->jobname) ?></td>
		</tr>
<?php
		}
?>
	</table>
	<p>
<?php
		if($nodes_found > $home_max_number_of_nodes) {
?>
	<a href="nodes">..additional nodes were found</a><br />
<?php
		} // more jobs available
	} // found jobs
?>
	Detailed information about all your nodes is presented on the <a href="nodes">nodes</a> page.</p>
		<hr />


		<h2>Your team memberships</h2>
		<?php
			$html = array();
			foreach($_SESSION["groups"] as $gid => $ginfo)
				$html[] = '<a href="teams?id='. $gid  .'">'. htmlspecialchars($ginfo["group"]) .'</a>';

			if(count($html))
				echo "<p>". implode(", ", $html) ."</p>";
		?>
		<p>Create new teams or invite other people to your team on the <a href="teams">Teams</a> page.</p>

<?php if($invite_only) { ?>
		<hr />
		<h1>Invite friends</h1>
		<p>This service is <a href="frequently-asked-questions#invite">invite only</a>. As an existing user you're able to invite a few friends here.</p>
		<p>You get a maximum of five invites and they're earned based on the total work your <a href="nodes">nodes</a> have done.<br />
		Below are information on the invite codes you've been assigned and their status.</p>

		<p><u>Status</u><br />
		# Earned: <strong><?= $_SESSION["u"]->invites_earned ?></strong><br />
		# Sent: <strong><?= $_SESSION["u"]->invites_sent ?></strong><br />

<?php
	$q = "SELECT * FROM invites WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
	if(($r = @$m->query($q)) !== FALSE && $r->num_rows) {
?>
		<table class="jobs">
			<tr>
				<th>Code</th>
				<th>Created</th>
				<th>Accepted</th>
			</tr>
<?php
		while($row = $r->fetch_object()) {
?>
			<tr>
				<td><?= htmlspecialchars($row->code) ?></td>
				<td><?= htmlspecialchars(date_friendly($row->dt_created)) ?></td>
				<td>
				<?php
					if($row->dt_accepted == NULL)
						echo '<span style="color: red; font-weight: bold">Not yet</span>';
					else
						echo '<span style="color: green; font-weight: bold">'. htmlspecialchars(date_friendly($row->dt_accepted)) .'</span>';
				?>
				</td>
			</tr>
<?php
		}
?>
		</table>
<?php
		$r->close();
	}
?>

		</p>
<?php } ?>
