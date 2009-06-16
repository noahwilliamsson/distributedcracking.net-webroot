<?php
	require("session-messages.php");


	if(($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == 0) {
?>
	<div class="note">
	<p>
	You're currently a member of the <em>Public</em> team, a default team assigned to all new accounts.<br />
	Members of the <em>Public</em> team work together on all jobs created by members of the team.<br />
	</p>
	<p>
	If you would rather keep your jobs private, or not have your nodes work on the public jobs, you need to <a href="account#upgrade">upgrade your account</a>. This is a one-time cost of <strong>$<?= $premium_account_price ?> USD</strong>.
	</p>
	</div>
<?php
	}
?>
	<h2>Your team memberships</h2>
	<p>A star after the username denotes a <a href="frequently-asked-questions#groupadmin">team administrator</a>.</p>
<?php
	foreach($_SESSION["groups"] as $gid => $ginfo) {
?>
	<h3><?= htmlspecialchars($ginfo["group"]) ?> (<a href="teams?id=<?= $gid ?>"><?php if($ginfo["admin"] == 1) echo "Manage"; else echo "Details"; ?></a>)</h3>
	<table class="nodes">
	<tr>
		<th>Created</th>
		<th>Members</th>
	</tr>
	<tr>
		<td><?= date_friendly($ginfo["dt_created"]) ?></td>
		<td>
<?php
	if($gid != 1) {
		$q = "SELECT group_admin, username FROM group_members LEFT JOIN users ON user_id=users.id WHERE group_id='". $m->escape_string($gid) ."' ORDER BY username";
		if(($r = $m->query($q)) !== FALSE) {
			if($r->num_rows == 0)
				echo 'This group has no members.</p>';
			$i = 0;
			while($row = $r->fetch_object()) {
				if($i++ != 0)
					echo ", ";
	
				echo htmlspecialchars($row->username);
				if($row->group_admin == 1)
					echo "*";
			}
		}
		$r->close();
	}
	else
		echo htmlspecialchars("<members are not disclosed for the Public team>");
?>
		</td>
	</tr>
	</table>
<?php
	} // foreach
?>
	<hr />

	<h2>Create new team</h2>
	<p>Collaborate and share jobs with other users by <a href="teams?id=0">creating a new team</a>.<br />You can invite your friends by providing them with the <em>invite code</em> that's available on the page where you <em>Manage</em> the team. The team's invite code can only be seen by its team administrators.</p>

	<hr />

	<h2 id="join">Join teams with an invitation code</h2>
	<p>Did you receive an invitation code for a team by a friend?<br />
	Paste it here and click the button to join the team.</p>
	<form action="teams" method="post">
	<div>
	        <label for="code">Invitation code</label><br />
	        <input type="text" id="code" name="invite_code" value="" />
	</div>
	<div>
	        <input type="submit" name="join" value="Join team" />
	</div>
	</form>

