		<h1>Create account</h1>
<?php
        if(!empty($page_error))
                echo '<p style="color:red">'. htmlspecialchars($page_error) ."</p>";
?>

		<p>Choose a username and a password. We'll take care of the rest.</p>
		<form action="create" method="post">
		<div>
			<label for="username">Username</label><br />
			<input type="text" name="u" id="username" value="" />
		</div>
		<div>
			<label for="password">Password</label><br />
			<input type="password" name="p" id="password" value="" />
		</div>
<?php if($invite_only) { ?>
		<div>
			<label for="invite">Invite code</label> (it's required for now, see the <a href="frequently-asked-questions#invite">FAQ</a> entry)<br />
			<input type="text" name="i" id="invite" value="" />
		</div>
<?php } ?>
		<div>
			<input type="submit" value="Create account!" />
		</div>
		</form>
