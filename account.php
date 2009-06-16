<h1>Account details</h1>
<table class="nodes">
	<tr>
		<th>Status</th>
		<th>Created</th>
		<th>Total nodes</th>
	</tr>
	<tr>
		<td>
			<?php if(($_SESSION["u"]->user_flags & USER_FLAG_PREMIUM) == USER_FLAG_PREMIUM) echo "Premium account"; else echo "Standard account, limits enforced"; ?>
		</td>
		<td><?= htmlspecialchars(date_friendly($_SESSION["u"]->dt_created)) ?></td>
		<td>
			<?php
				$q = "SELECT COUNT(*) AS num FROM nodes WHERE user_id='". $m->escape_string($_SESSION["u"]->id) ."'";
				if(($r = @$m->query($q)) !== FALSE && ($row = $r->fetch_object()) != NULL)
					echo $row->num;
				else
					echo htmlspecialchars("<an error occured>");
			?>
		</td>
	</tr>
</table>

<h2 id="upgrade">Upgrade to a premium account</h2>
<p>A premium account has the following features and benefits:
<ul>
	<li>Your may opt out of the <a href="teams?id=1">Public</a> group
		<ul>
			<li>Your jobs do not have to be shared with the public which gives you additional privacy</li>
			<li>Better performance when your nodes aren't shared with the public. They'll work exclusively for you.</li>
		</ul>
	</li>
	<li>You can view all the found plaintexts for a job, not only the first 100</li>
	<li>You may register more than 2 nodes with your account</li>
	<li>Detailed statistics on your jobs and nodes (planned, not yet implemented)</li>
	<li>Support further development of this service</li>
</ul>
<p>It's available at <strong>$<?= $premium_account_price ?> USD</strong> as a one time fee. Click the button below to continue and upgrade to a premium account.</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="business" value="<?= $paypal_mail_to ?>" />
<input type="hidden" name="item_name" value="Premium account" />
<input type="hidden" name="item_number" value="1" />
<input type="hidden" name="amount" value="<?= $premium_account_price ?>" />
<input type="hidden" name="shipping" value="0.00" />
<input type="hidden" name="no_shipping" value="1" />
<input type="hidden" name="return" value="<?= $root_url ?>paypal" />
<input type="hidden" name="cancel_return" value="<?= $root_url ?>paypal?cancel=1" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="currency_code" value="USD" />
<input type="hidden" name="tax" value="0.00" />
<input type="hidden" name="lc" value="US" />
<input type="hidden" name="bn" value="PP-BuyNowBF" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>

