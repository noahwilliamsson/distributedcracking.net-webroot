<?php
	/*
	 * Make sure the user indeed has access to the job $_GET["id"]
	 *
	 */
	
	$q = "SELECT * FROM nodes WHERE id='". $m->escape_string($_GET["id"]) ."' AND user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
	$node = NULL;
	if(($r = @$m->query($q)) !== FALSE) {
		$node = $r->fetch_object();
		$r->close();
	}


	// Print out session messages (info, errors)
	require("session-messages.php");


	if($node == NULL) {
?>
	<h1>Access denied</h1>
	<div class="note">
		<p>Nonexistant node or access denied. God knows what and we're certainly not telling.</p>
	</div>
<?php
	}
	else {
?>
	<h1>Node information for <em><?= htmlspecialchars(preg_replace("/^$/", "<no name>", $node->nodename)) ?></em></h1>

	<h2>Set node's nickname</h2>
	<form action="nodes?id=<?= $_GET["id"] ?>" method="post">
		<div>
			<label for="nodename">Nickname</label><br />
			<input type="text" name="nodename" id="nodename" value="<?= htmlspecialchars($node->nodename) ?>" />
		</div>
		<div>
			<input type="submit" value="Update node" />
		</div>
	</form>


	<h2>Statistics</h2>
	<p>(To be written.. scheduled for week 30)</p>
<!--
	<table class="sheet">
		<tr>
			<th>Hash type</th>
			<th>Total hashes</th>
			<th>Cracked</th>
			<th>Last worked on</th>
			<th>Groups</th>
		</tr>
		<tr>
			<td><?= htmlspecialchars($job->hashtype) ?></td>
			<td><?= $job->summary_numhashes ?></td>
			<td><?= $job->summary_numcracked ?></td>
			<td><?= date_friendly($job->dt_lastactive) ?></td>
			<td>
			<?php
			?>
			</td>
		</tr>
	</table>
	<hr />
-->

<?php
	}
?>
