<?php
	// Fetch the user's jobs
	$jobs = array();
	$q = "SELECT * FROM jobs WHERE owner_user_id='". $m->escape_string($_SESSION["u"]->id) ."' AND (jobflags & ". (JOB_FLAG_DELETED) .") != ". (JOB_FLAG_DELETED) ."";
	if(($r = @$m->query($q)) !== FALSE) {
		while($row = $r->fetch_object())
			$jobs[] = $row;
		$r->close();
	}


	// Fetch jobs from the user's team memberships, excluding those found in the above query
	$shared_jobs = array();
	$q = "SELECT jobs.* FROM group_jobs LEFT JOIN jobs ON job_id=jobs.id WHERE group_id IN ('". implode("', '", array_keys($_SESSION["groups"])) ."') AND owner_user_id!='". $m->escape_string($_SESSION["u"]->id) ."' AND (jobflags & ". (JOB_FLAG_DELETED) .") != ". (JOB_FLAG_DELETED) ." ORDER BY jobname";

	if(($r = @$m->query($q)) !== FALSE) {
		while($row = $r->fetch_object())
			$shared_jobs[] = $row;
		$r->close();
	}



        // Display diagnostic messages, if any
        if(isset($_SESSION["info"])) {
                $msg = $_SESSION["info"];
                unset($_SESSION["info"]);
?>
        <div class="info">
                <p><?= htmlspecialchars($msg) ?></p>
        </div>
<?php
        }
        else if(isset($_SESSION["error"])) {
                $msg = $_SESSION["error"];
                unset($_SESSION["error"]);
?>
        <div class="note">
                <p><?= htmlspecialchars($msg) ?></p>
        </div>
<?php
        }
?>

	<h1>Your jobs</h1>
<?php
	if(count($jobs) == 0) {
?>
	<div class="info">
		<p>You haven't created any jobs yet.<br />Continue and <a href="jobs#create">create a new job</a>.</p>
	</div>
<?php
	}
	else {
?>
	<table class="jobs">
		<tr>
			<th>Description</th>
			<th>Hash type</th>
			<th>Hashes</th>
			<th>Cracked</th>
			<th>Std CPU time</th>
			<th>Status</th>
		</tr>
<?php
		$job_counter = 0;
		foreach($jobs as $row) {
			$job_counter++;

			$cached_num_cracked_html = "";
			if(!isset($_SESSION["c"]["jobs"]))
				$_SESSION["c"]["jobs"] = array();

			if(!isset($_SESSION["c"]["jobs"][$row->id]))
				$_SESSION["c"]["jobs"][$row->id] = $row;

			if($row->summary_numcracked > $_SESSION["c"]["jobs"][$row->id]->summary_numcracked) {
				$cached_num_cracked_html = ' (<span style="color:blue">+'. ($row->summary_numcracked  - $_SESSION["c"]["jobs"][$row->id]->summary_numcracked) .'</span>)';
			}

			$_SESSION["c"]["jobs"][$row->id] = $row;


		$total_timespent = 0;
		$total_rounds = 0;
		$total_std_cpu_time = 0;
		$q = "SELECT UNIX_TIMESTAMP(completed) - UNIX_TIMESTAMP(acquired) AS timespent, incremental_rounds, num_hashes FROM packets WHERE job_id='". $m->escape_string($row->id) ."' AND done=1";
		if(($r = $m->query($q)) !== FALSE) {
			while($packetrow = $r->fetch_object()) {
				$total_timespent += $packetrow->timespent;
				$total_rounds += $packetrow->incremental_rounds;
				if($incremental_packet_size[strtolower($row->hashtype)])
					$total_std_cpu_time += floor($packetrow->incremental_rounds * $packetrow->num_hashes / $incremental_packet_size[strtolower($row->hashtype)]);
			}
			$r->close();
		}

		$percent_html = "";
		if($row->summary_numcracked != $row->summary_numhashes && $row->summary_numcracked != 0)
			$percent_html = ' <span style="font-size: 0.8em">('. sprintf("%.01f%%", 100.0 * $row->summary_numcracked / $row->summary_numhashes) .')</span>';

?>
		<tr>
			<td><?= str_replace(" ", "&nbsp;", sprintf("% 2d.", $job_counter)) ?> <a href="jobs?id=<?= $row->id ?>" title="Change settings for this job"><?= htmlspecialchars($row->jobname) ?></a></td>
			<td><?= htmlspecialchars($row->hashtype) ?></td>
			<td><?= htmlspecialchars($row->summary_numhashes) ?></td>
			<td><?= htmlspecialchars($row->summary_numcracked) . $cached_num_cracked_html . $percent_html ?></td>
			<td align="right"><?= htmlspecialchars(time_friendly($total_std_cpu_time, TRUE)) ?></td>
			<td>
				<?php
					if(($row->jobflags & JOB_FLAG_ACTIVE) == JOB_FLAG_ACTIVE)
						echo '<span style="color:green">ACTIVE</span>';
					else if(($row->jobflags & JOB_FLAG_DONE) == JOB_FLAG_DONE)
						echo '<strong>DONE</strong>';
					else
						echo '<span style="color:red">PAUSED</span>';
				?>
			</td>
		</tr>
<?php
		}
?>
	</table>
<?php
	} // if count jobs == 0
?>
<hr />



<h2>Shared jobs</h2>

<?php
	if(count($shared_jobs) == 0) {
?>
	<div class="info">
		<p>You're not sharing jobs with any other <a href="teams">team members</a> yet.</p>
	</div>
<?php
	}
	else {
?>
	<table class="jobs">
		<tr>
			<th>Description</th>
			<th>Hash type</th>
			<th>Hashes</th>
			<th>Cracked</th>
			<th>Std CPU time</th>
			<th>Status</th>
		</tr>
<?php
		$job_counter = 0;
		foreach($shared_jobs as $row) {
			$job_counter++;

			if($row->jobname == "")
				$row->jobname = "<no name>";

			$cached_num_cracked_html = "";
			if(!isset($_SESSION["c"]["jobs"]))
				$_SESSION["c"]["jobs"] = array();

			if(!isset($_SESSION["c"]["jobs"][$row->id]))
				$_SESSION["c"]["jobs"][$row->id] = $row;

			if($row->summary_numcracked > $_SESSION["c"]["jobs"][$row->id]->summary_numcracked) {
				$cached_num_cracked_html = ' (<span style="color:blue">+'. ($row->summary_numcracked  - $_SESSION["c"]["jobs"][$row->id]->summary_numcracked) .'</span>)';
			}

			$_SESSION["c"]["jobs"][$row->id] = $row;


			// Fetch amount of time spent, expressed in standard CPU time
			$total_timespent = 0;
			$total_rounds = 0;
			$total_std_cpu_time = 0;
			$q = "SELECT UNIX_TIMESTAMP(completed) - UNIX_TIMESTAMP(acquired) AS timespent, incremental_rounds, num_hashes FROM packets WHERE job_id='". $m->escape_string($row->id) ."' AND done=1";
			if(($r = $m->query($q)) !== FALSE) {
				while($packetrow = $r->fetch_object()) {
					$total_timespent += $packetrow->timespent;
					$total_rounds += $packetrow->incremental_rounds;
					if($incremental_packet_size[strtolower($row->hashtype)])
						$total_std_cpu_time += floor($packetrow->incremental_rounds * $packetrow->num_hashes / $incremental_packet_size[strtolower($row->hashtype)]);
				}
				$r->close();
			}

			$percent_html = "";
			if($row->summary_numcracked != $row->summary_numhashes && $row->summary_numcracked != 0)
				$percent_html = ' <span style="font-size: 0.8em">('. sprintf("%.01f%%", 100.0 * $row->summary_numcracked / $row->summary_numhashes) .')</span>';
?>
		<tr>
			<td><?= str_replace(" ", "&nbsp;", sprintf("% 2d.", $job_counter)) ?> <a href="jobs?id=<?= $row->id ?>" title="Change settings for this job"><?= htmlspecialchars($row->jobname) ?></td>
			<td><?= htmlspecialchars($row->hashtype) ?></td>
			<td><?= htmlspecialchars($row->summary_numhashes) ?></td>
			<td><?= htmlspecialchars($row->summary_numcracked) . $cached_num_cracked_html . $percent_html ?></td>
			<td align="right"><?= htmlspecialchars(time_friendly($total_std_cpu_time, TRUE)) ?></td>
			<td>
				 <?php
                                        if(($row->jobflags & JOB_FLAG_ACTIVE) == JOB_FLAG_ACTIVE)
                                                echo '<span style="color:green">ACTIVE</span>';
                                        else if(($row->jobflags & JOB_FLAG_DONE) == JOB_FLAG_DONE)
                                                echo '<strong>DONE</strong>';
                                        else    
                                                echo '<span style="color:red">PAUSED</span>';
                                ?>

			</td>
		</tr>
<?php
		}

?>
	</table>
<?php
	} // foreach shared job


	// Store cache with cached number of cracked hashes
	session_cache_save();
?>
<hr />


<h1 id="create">Create new job</h1>
<p>Upload a file with hashes and select a mode of attack to create a new job.</p>
<div>
<form action="jobs" method="post" enctype="multipart/form-data" class="sheet">
<div>
	<label for="jobname">Job description</label><br />
	<input type="text" id="jobname" name="jobname" value="" />
</div>
<div>
	<label for="hashfile">File with hashes</label> (See below for some valid file formats)<br />
	<input type="file" id="hashfile" name="hashfile" />
</div>
<div>
	<label for="mode">Attack mode</label><br />
	<select name="mode" id="mode">
<?php
	foreach($attack_modes as $id => $a) {
?>
		<option value="<?= $id ?>"><?= htmlspecialchars($a["text"]) ?></option>
<?php
	}
?>
	</select>
</div>
<div>
	<label for="teams">Share job with these teams</label><br />
	<select name="groups[]" id="teams" multiple="multiple" style="width: 200px">
<?php
	$groups = user_get_groups($_SESSION["u"]->id);
	foreach($groups as $g) {
		$sel_html = ' selected="selected"';
		if($g->id == 1 && ($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == 0)
			$g->groupname .= " (cannot be deselected)";
		else if($g->id == 1 && ($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == USER_FLAG_PREMIUM)
			$sel_html = "";

?>
		<option value="<?= $g->id ?>"<?= $sel_html ?>><?= htmlspecialchars($g->groupname) ?></option>
<?php
	}
?>
	</select>
</div>
<!--
<div>
	<script type="text/javascript">
		var adv = 0;
	</script>
	<p><a href="#" id="advancedtext" onclick="adv++; var e=document.getElementById('advanced'); if(adv % 2 == 1) { e.style.display='block'; var e=document.getElementById('advancedtext');e.innerHTML='Hide advanced options'; } else { e.style.display='none'; e=document.getElementById('advancedtext');e.innerHTML='Show advanced options'; } return false">Show advanced options</a></p>
	<div id="advanced" style="display:none">
	<label for="recoveryfile">john.rec file</label> (Restore a previous John the Ripper session; Not yet done, WORK IN PROGRESS!)<br />
	<input type="file" id="recoveryfile" name="recoveryfile" />
	</div>
</div>
-->
<div>
	<input type="submit" value="Submit job" />
</div>
<div>
</div>

	
</form>
</div>

<p>The uploaded file should either be a list of hashes (one per row) or in a UNIX style /etc/passwd format.<br />
<strong>Note:</strong> In the case of a UNIX style /etc/passwd format we only care about the second field, the hash.<br />
All other data such as the first field (the username) and eventual uid/gid/gecos information is ignored. We do not store anything but the hashes on our servers for security and privacy reasons.</p>

<h4>Example: Traditional UNIX style /etc/passwd</h4>
<p class="sheet">
foo:GBEQW4rsCHLBs:0:0:User foo, +555 1234:/home/foo:/bin/sh<br />
bar:GBEQW4rsCHLBs:0:0:User bar, +555 5677:/home/bar:/bin/sh
</p>

<h4>Example: List of raw-MD5 hashes, one per row</h4>
<p class="sheet">
00bfc8c729f5d4d529a412b12c58ddd2<br />
00bfc8c729f5d4d529a412b12c58ddd2<br />
00bfc8c729f5d4d529a412b12c58ddd2
</p>
