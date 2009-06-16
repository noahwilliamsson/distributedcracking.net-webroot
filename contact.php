<h1>Contact</h1>
<p>We'd love to hear from you!</p>
<form action="contact" method="post">
<div>
	<label for="from">Your e-mail</label> (optional, but needed for feedback)<br />
	<input type="text" id="from" name="from" value="" />
</div>
<div>
	<label for="text">Message</label><br />
	<textarea name="text" rows="10" cols="80"></textarea>
</div>
<div>
	<input type="submit" value="Send message" />
</div>
</form>
<p>Of course, you can use plain email too although it's not as safe as sending a message over secured HTTP.<br />
Send feedback or other inquiries to <a href="mailto:<?= $contact_mail_to ?>?subject=Feedback"><?= htmlspecialchars(str_replace("@", " at ", $contact_mail_to)) ?></a>.
</p>
<p>Here's our <a href="contact.asc">PGP key</a> (B1921B72).</p>
<div class="sheet">
<p> <strong>Fingerprint</strong> B1E1 0200 8D22 2E01 947F  0180 2193 DD9B B192 1B72 </p>
</div>
