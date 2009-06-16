		<h1>You need to be logged in to perform this action</h1>
<?php
        if(!empty($page_error))
                echo '<p style="color:red">'. htmlspecialchars($page_error) ."</p>";
?>
	
		<p>Please proceed to the <a href="login" accesskey="l">login</a> page to continue.</p>

		<h1>Don't have an account yet?</h1>
		<p><a href="create" accesskey="c">Create an account</a> and start cracking hashes immediately!</p>
