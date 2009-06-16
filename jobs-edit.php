<?php
	/*
	 * Make sure the user indeed has access to the job $_GET["id"]
	 *
	 */

	$job = NULL;
	$job_groups = array();

	if(($job_id = intval($_GET["id"])) > 0) {
		// Fetch the job if it exists
		$q = "SELECT * FROM jobs WHERE id='". $m->escape_string($job_id) ."' AND (jobflags & ". (JOB_FLAG_DELETED) .") != ". (JOB_FLAG_DELETED);
		if(($r = @$m->query($q)) !== FALSE) {
			$job = $r->fetch_object();
			$r->close();
		}

		if($job != NULL) {
			$q = "SELECT groups.* FROM group_jobs LEFT JOIN groups ON group_id=groups.id WHERE job_id='". $m->escape_string($job_id) ."' ORDER BY groupname";
			if(($r = @$m->query($q)) !== FALSE) {
				while($row = $r->fetch_object())
					$job_groups[$row->id] = $row;
				$r->close();
			}
		}

		// If the user isn't the owner of the job check if he's member of
		// a group who have access to it
		if($job != NULL && $job->owner_user_id != $_SESSION["u"]->id) {
			$user_has_access = FALSE;
			foreach(user_get_groups($_SESSION["u"]->id) as $g) {
				if(!isset($job_groups[$g->id]))
					continue;

				$user_has_access = TRUE;
				break;
			}

			if(!$user_has_access)
				$job = NULL;
		}
	}


	// Print out session messages (info, errors)
	require("session-messages.php");


	if($job == NULL) {
?>
	<h1>Access denied</h1>
	<div class="note">
		<p>Nonexistant job or access denied. God knows what and we're certainly not telling.</p>
	</div>
<?php
	}
	else {

		$total_timespent = 0;
		$total_rounds = 0;
		$total_std_cpu_time = 0;
		$q = "SELECT UNIX_TIMESTAMP(completed) - UNIX_TIMESTAMP(acquired) AS timespent, incremental_rounds, num_hashes FROM packets WHERE job_id='". $m->escape_string($job->id) ."' AND done=1";
		if(($r = $m->query($q)) !== FALSE) {
			while($row = $r->fetch_object()) {
				// $incremental_packet_size[$job->hashtype]
				// $rounds = $incremental_packet_size[$hashtype] * $incremental_avg_crack_time / $num_hashes_uncracked;
				$total_timespent += $row->timespent;
				$total_rounds += $row->incremental_rounds;
				if($incremental_packet_size[strtolower($job->hashtype)])
					$total_std_cpu_time += floor($row->incremental_rounds * $row->num_hashes / $incremental_packet_size[strtolower($job->hashtype)]);
			}
			$r->close();
		}
?>
	<h1>Job <em><?= htmlspecialchars($job->jobname) ?></em></h1>
	<h2>Details</h2>
	<table class="nodes" width="100%">
		<tr>
			<th>Kind</th>
			<th>Hashes</th>
			<th>Last worked on</th>
			<th>Total time spent</th>
			<th>Status</th>
			<th>Shared with</th>
		</tr>
		<tr>
			<td><?= htmlspecialchars($job->hashtype) ?></td>
			<td><?= "$job->summary_numcracked / $job->summary_numhashes" ?></td>
			<td><?= date_friendly($job->dt_lastactive) ?></td>
			<td>
				<?= time_friendly($total_timespent) ?>
				<br />
				Std CPU: <?= time_friendly($total_std_cpu_time) ?>
			</td>
			<td>
			<?php
                                        if(($job->jobflags & JOB_FLAG_ACTIVE) == JOB_FLAG_ACTIVE)
                                                echo '<span style="color:green">ACTIVE</span>';
                                        else if(($job->jobflags & JOB_FLAG_DONE) != 0)
                                                echo '<strong>DONE</strong>';
                                        else    
                                                echo '<span style="color:red">PAUSED</span>';
                                ?>
			</td>

			<td>
			<?php
				$groupnames = array();
				$i = 0;
				foreach($job_groups as $g) {
					// Don't display group the user doesn't have access to
					if(!isset($_SESSION["groups"][$g->id]))
						continue;

					if($i++ != 0) echo ", ";
					echo '<a href="teams?id='. $g->id .'">'. htmlspecialchars($g->groupname) .'</a>';
				}
				if(count($job_groups) == 0)
					echo htmlspecialchars("<not shared>");
			?>
			</td>
		</tr>
	</table>
	<hr />

	<h2>Download cracked hashes</h2>
	<p><strong>Note:</strong> Cracked hashes are delivered in pairs on a line by line basis, i.e one <em>hash:plaintext</em> pair per line.</p>
	<form action="export-hashes?id=<?= $_GET["id"] ?>" method="post">
		<input type="submit" name="gethashes" value="Download plaintexts" />
	</form>

	<a href="contact">Contact us</a> if you have suggestions on other output formats.

<?php
	$q = "SELECT hash, plaintext FROM hashes WHERE job_id='". $m->escape_string($_GET["id"]) ."' AND dt_cracked > DATE_SUB(NOW(), INTERVAL 2 WEEK) ORDER BY dt_cracked DESC LIMIT 20";
	if(($r = @$m->query($q)) !== FALSE) {
		if($r->num_rows > 0) {
?>
		<h3>Recently cracked</h3>
		<table class="nodes">
		<tr><th>Plaintext</th><th>Hash</th></tr>
<?php
		while($row = $r->fetch_object())
			echo "<tr><td>". htmlspecialchars($row->plaintext) ."</td><td>". htmlspecialchars($row->hash) . "</td></tr>\n";
?>
		</table>
<?php
		}

		$r->close();
	}
?>


	<hr />
	<h2>Modify job</h2>
	<form action="jobs-edit?id=<?= $_GET["id"] ?>" method="post">
		<div>
<?php
	if(($job->jobflags & JOB_FLAG_DONE) == 0) {
?>
			<label for="activeflag">Job status</label><br />
			<select name="activeflag" id="activeflag">
				<option value="1"<?php if(($job->jobflags & JOB_FLAG_ACTIVE) == JOB_FLAG_ACTIVE) echo ' selected="selected"'; ?>>Active, let nodes work on it</option>
				<option value="0" <?php if(($job->jobflags & JOB_FLAG_ACTIVE) == 0) echo ' selected="selected"'; ?>>Pause, prevent nodes from working on it</option>
			</select>
		</div>
<?php
		}
?>
		<div>
			<label for="jobname">Jobname</label><br />
			<input type="text" name="jobname" id="jobname" value="<?= htmlspecialchars($job->jobname) ?>" />
		</div>
		<div>
			<input type="submit" name="modify" value="Update job" />
		</div>
	</form>
<?php
	}
?>
	<hr />
	<h2>Delete job</h2>
	<form action="jobs-edit?id=<?= $_GET["id"] ?>" method="post">
	<p>This action cannot be undone.</p>
	<div>
		<input type="submit" name="delete" value="Delete job" />
	</div>
	</form>

	<hr />
	<h2>Active nodes the last week</h2>
<?php
	$q = "SELECT nodename, cpuinfo, MAX(completed) completed, COUNT(*) num_packets FROM packets LEFT JOIN nodes ON node_id=nodes.id  WHERE job_id='". $m->escape_string($job_id) ."' AND acquired > DATE_SUB(NOW(), INTERVAL 1 WEEK) AND done=1 GROUP BY node_id ORDER BY completed DESC";

	if(($r = @$m->query($q)) !== FALSE) {
		if($r->num_rows > 0) {
?>
	<table class="nodes" width="100%">
		<tr>
			<th>Name</th>
			<th>CPU info</th>
			<th>Last seen</th>
			<th>Packets completed</th>
		</tr>
<?php
			while($row = $r->fetch_object()) {
				if($row->nodename == NULL)
					$row->nodename = "<no name>";
				if($row->jobname == NULL)
					$row->jobname = "<nothing yet>";

?>
		<tr>
			<td><?= htmlspecialchars($row->nodename) ?></td>
			<td><?= htmlspecialchars(cpu_short($row->cpuinfo)) ?></td>
			<td><?= htmlspecialchars(date_friendly($row->completed)) ?></td>
			<td><?= $row->num_packets ?></td>
		</tr>
<?php
			}
?>
	</table>
<?php
		}
		else {
?>
	<div class="note">
		<p>No nodes have been working on this job the last week.</p>
	</div>
<?php
		}
	}
?>
	<hr />

