<?php
	if(isset($_GET["cancel"])) {
?>
<h1>Upgrade canceled</h1>
<p>You canceled the payment.</p>
<?php
	}
	else {
		log_event("User paid for the services");

		$q = "UPDATE users SET user_flags = user_flags | ". USER_FLAG_PREMIUM ." WHERE id='". $m->escape_string($_SESSION["u"]->id) ."'";
		if(@$m->query($q) === FALSE)
			log_event("Failed to reflect payment in users-table: $m->error\nSQL: $q");

		$_SESSION["u"]->user_flags |= USER_FLAG_PREMIUM;
?>
<h1>Your account has been upgraded</h1>
<p>Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you. You may log into your account at <a href="https://www.paypal.com/row" target="_blank">www.paypal.com/row</a> to view details of this transaction.</p>

<?php
	}
?>

<p><a href="/">Continue to the frontpage</a></p>
