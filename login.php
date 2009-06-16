		<h1>Login</h1>
<?php
	if(!empty($page_error))
		echo '<p style="color:red">'. htmlspecialchars($page_error) ."</p>";
?>
		<form action="login" method="post">
		<div>
			<label for="username">Username</label>
			<input type="text" name="u" id="username" value="" />
		</div>
		<div>
			<label for="password">Password</label>
			<input type="password" name="p" id="password" value="" />
		</div>
		<div>
			<input type="submit" value="Log in" />
		</div>
		</form>
		<p>
			Don't have an account yet?<br />
			<a href="create">Create an account</a> and start cracking immediately.
		</p>
<script type="text/javascript">
	var e = document.getElementById('username');
	if(e && e.value.length == 0) e.focus();
</script>
