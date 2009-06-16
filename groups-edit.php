<?php
	/*
	 * XXX - Make sure the user is indeed a member of $_GET["id"]
	 * Otherwise, display the page for creating a new group. :>
	 *
	 */

	$group = NULL;
	$group_members = array();
	if(($gid = intval($_GET["id"])) > 0) {

		// Fetch group info
		$q = "SELECT * FROM groups WHERE id='". $m->escape_string($gid) ."'";
		if(($r = @$m->query($q)) !== FALSE) {
			$group = $r->fetch_object();
			$r->close();
		}


		// Fetch group members
		$user_found_in_group = FALSE;
		$q = "SELECT * FROM group_members WHERE group_id='". $m->escape_string($gid) ."'";
		if(($r = @$m->query($q)) !== FALSE) {

			// If the user is not a member of the group, $group_members will be empty
			// Otherwise it will contain information about the group's members
			while($row = $r->fetch_object()) {
				$group_members[$row->user_id] = $row;

				// Signal that we're indeed a member of this group!
				if($row->user_id == $_SESSION["u"]->id)
					$user_found_in_group = TRUE;
			}

			$r->close();
		}

		// Empty the array if the user wasn't among the group's members
		if(!$user_found_in_group)
			$group_members = array();
	}



        // Print out session messages (info, errors)
        require("session-messages.php");


	if(count($group_members) == 0) {
?>
		<h1>Create new team</h1>
<?php
        if(!empty($page_error))
                echo '<p style="color:red">'. htmlspecialchars($page_error) ."</p>";
?>
		<p>Choose a name for the new team. We'll take care of the rest.</p>
		<form action="teams?id=0" method="post">
		<div>
			<label for="team">Team name</label><br />
			<input type="text" name="g" id="team" value="" />
		</div>
		<div>
			<input type="submit" value="Create team!" />
		</div>
		</form>
<?php
	}
	else {
?>
	<h1>Manage team <em><?= htmlspecialchars($group->groupname) ?></em></h1>
	<table class="jobs">
		<tr>
			<th>Username</th>
			<th>Joined</th>
			<th>Team administrator</th>
			<th>Manage</th>
		</tr>
<?php
        $q = "SELECT group_members.*, username FROM group_members LEFT JOIN users ON user_id=users.id WHERE group_id='". $m->escape_string($gid) ."' ORDER BY username";
        if(($r = $m->query($q)) !== FALSE) {
		while($row = $r->fetch_object()) {

			if($row->group_id != 1 || ($row->group_id == 1 && $row->user_id == $_SESSION["u"]->id)) {
?>
		<tr>
			<td><?= htmlspecialchars($row->username) ?></td>
			<td><?= htmlspecialchars(date_friendly($row->dt_added)) ?></td>
			<td><?php if($row->group_admin) echo "Yes"; else echo "No"; ?></td>
			<td>
			<?php
				if($row->user_id == $_SESSION["u"]->id) {
			?>
				<a href="teams?id=<?= $row->group_id ?>&amp;remove=<?= $row->user_id ?>">Leave team</a>
			<?php
				}
				else if($_SESSION["groups"][$row->group_id]["admin"] == 1) {
			?>
				<a href="teams?id=<?= $row->group_id ?>&amp;remove=<?= $row->user_id ?>">Remove user</a>
			<?php
				}
				else
					echo "-";
			?>
			</td>

<?php
			} // if should show info
		} // while group members
	}
?>
	</table>

<?php
		if($_SESSION["groups"][$_GET["id"]]["admin"] == 1) {
?>
        <hr />
        <h2>Invite users to your teams</h2>
        <p>By inviting other users to your team(s) you will be able to share <a href="jobs">jobs</a> with all members of a those teams. All nodes in the team(s) will work on jobs shared among the team to speed up the cracking of the hashes.</p>

	<h3>This team's invitation code</h3>
	<div class="sheet">
		<?= htmlspecialchars($_SESSION["groups"][$_GET["id"]]["invite_code"]) ?>
	</div>

<?php
		}
	} // if edit team
?>
