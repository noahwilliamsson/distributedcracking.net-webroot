<?php
	if(isset($_GET["action"]) && $_GET["action"] == "ok") {
?>
	<h1>Thank you</h1>
	<p>Your donation has been received. Thank's a lot for supporting us!</p>
	<p><a href="/">Continue to the frontpage</a></p>
<?php
	}
	// else if(isset($_GET["action"]) && $_GET["action"] == "cancel") {
	else {
		if(isset($_GET["action"]) && $_GET["action"] == "cancel") {
?>
	<div class="note">
		Donation canceled. :(
	</div>
<?php
		}
?>
	<h1>Donate to allow us to improve this service</h1>
	<p>This <a href="frequently-asked-questions#about">service</a> is run as a hobby project. That means we have limited resources when it comes to bandwidth, CPU power and time developing the services. Your donations would be greatly appreciated. Help us help the world to realize passwords are a <strong>bad idea</strong>.</p>
	<h2>What will donation be used for?</h2>
	<ul>
		<li>Hosting costs
			<ul>
				<li>Co-location of servers</li>
				<li>Bandwidth for the site and client software communication</li>
				<li>Off-site backup</li>
			</ul>
		</li>
		<li>Servers
			<ul>
				<li>We need modern hardware to be able to provide reliable services</li>
				<li>CPU power to do backend processing and to contribute CPU time to public jobs</li>
			</ul>
		</li>
	</ul>

	<p>Click the button below and specify how much you would like to donate on the subsequent pages.</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
<input type="hidden" name="encrypted" value="--YOU-NEED-TO-EDIT-THIS-VALUE--" />
</form>

<?php
	}
?>
