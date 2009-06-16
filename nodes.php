<?php
	$nodes = array();
	$q = "SELECT nodes.*, jobname FROM nodes LEFT JOIN jobs ON current_job_id=jobs.id WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."' ORDER BY nodes.dt_lastactive DESC";
	if(($r = @$m->query($q)) !== FALSE) {
		$nodes_count = $r->num_rows;
		$nodes_timespent = 0;


		while($row = $r->fetch_object()) {
			$q = "SELECT SUM(UNIX_TIMESTAMP(completed) - UNIX_TIMESTAMP(acquired)) AS timespent FROM packets WHERE node_id=$row->id AND done=1 ORDER BY acquired DESC";
			$r_ts = $m->query($q);
			$packets_summary = $r_ts->fetch_object();
			$r_ts->close();

			$row->timespent = time_friendly($packets_summary->timespent);
			if($packets_summary->timespent != NULL)
				$nodes_timespent += $t;

			$nodes[] = $row;
		}


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

	<h1>Your nodes</h1>
<?php
	if(count($nodes) == 0) {
?>
	<div class="info">
		<p>There are no registered nodes for your account. <a href="download">Download</a> the client software and register it with your account.</p>
	</div>
<?php
	}
	else {
?>
<?php
		foreach($nodes as $row) {
                        if($row->nodename == NULL)
                                $row->nodename = "<no name>";
                        if($row->jobname == NULL)
                                $row->jobname = "<nothing yet>";

?>
		<h3><?= htmlspecialchars($row->nodename .", ". cpu_short($row->cpuinfo)) ?> (<a href="nodes?id=<?= $row->id ?>" title="Edit <?= htmlspecialchars($row->nodename) ?>">Modify</a>)</h3>
		<table class="nodes">
			<tr>
				<th>Active job</th>
				<td><?= htmlspecialchars($row->jobname) ?></td>
			</tr>
			<tr>
				<th>Last seen</th>
				<td><?= htmlspecialchars(date_friendly($row->dt_lastactive)) ?></td>
			</tr>
			<tr>
				<th>First seen</th>
				<td><?= htmlspecialchars(date_friendly($row->dt_created)) ?></td>
			</tr>
<!--
			<tr>
				<th>Ciphers</th>
				<td><?= htmlspecialchars(str_replace(",", ", ", $row->ciphers)) ?></td>
			</tr>
-->
			<tr>
				<th>Time spent</th>
				<td><?= htmlspecialchars($row->timespent) ?></td>
			</tr>
		</table>
<?php
		}
?>
	</table>
<?php
	} // if count jobs == 0
?>
