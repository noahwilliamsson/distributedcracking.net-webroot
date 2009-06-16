<h1>Frequently asked questions</h1>
<ul>
	<li><a href="#about">About</a></li>
<?php if($invite_only) { ?>
	<li><a href="#invite">Invites</a></li>
<?php } ?>
	<li><a href="#passwords">Passwords</a></li>
	<li><a href="#privacy">Your privacy</a></li>
	<li><a href="#other">Other</a></li>
</ul>
<h2 id="about">About</h2>

<h3>What is distributedcracking.net?</h3>
<p>We're a service where you can upload a set of hashes (called a <em>job</em>) and have their strength tested by using common tools designed for that very purpose.</p>
<p>We're special because we provide a networked hash breaking system where many computers (<em>nodes</em>) can collaborate on trying to break the hashes.</p>
<p>
The web site and the client software is designed to provide a straight forward, easy to use interface to protect you from the pesky details of high performance hash breaking.
It also provides management measures such as users and groups to aid in the collaboration of hash cracking, for example between multiple users or departments.
</p>

<h3>Why would I use this service?</h3>
<p>Many people pick insecure passwords. It's a known fact, yet people continue to use password based authenticating aswell as picking bad (or differently bad) passwords. Using our serivces you, or your company, can verify the strength of your users' chosen passwords (if they're hashed), much quicker and easier than using ordinary tools designed for this purpose.</p>
<p>We're online, accessible over the web. Oh, and did we mention it's distributed so you're not limited to a single computer's performance to do it? And as a bonus, it doesn't suck!</p>

<h3>What hashes are supported?</h3>
<p>Please see the page about <a href="hashes">hashes</a>.</p>

<h3>Is this legal?</h3>
<p><strong>Short answer:</strong> According to our lawyer, yes!<br />
<strong>Longer answer:</strong> It might not be legal to use this service in all countries. YMMV.</p>

<h3>Who are you?</h3>
<p> </p>

<h3>How can I get in touch with you?</h3>
<p>On the bottom of all pages there's a link to the <a href="contact">Contact</a> page.</p>
<hr />


<?php if($invite_only) { ?>
<h2 id="invite">Invites</h2>
<h3>Why is this service invite only?</h3>
<p>The software run on the server side is kind of heavy when it comes to CPU power and bandwidth, something which is limited resources for us at the moment. It sucks, we know, but we're hoping more and more people will find it useful and pay for the service. If you would like to help, consider <a href="donate">donating</a> or upgrading to a <a href="account#upgrade">premium account</a>. :)</p>

<h3>How many invites do I get?</h3>
<p>Once you've been invited you get five invites after a couple of days of activity (i.e., contribution of CPU power). Those are all you get so choose your friends wisely.</p>
<hr />
<?php } ?>



<h2 id="passwords">Passwords</h2>
<h3>What's a good password?</h3>
<p>Microsoft have some good information on <a target="_blank" href="http://www.microsoft.com/protect/yourself/password/create.mspx">Strong passwords: How to create and use them</a>.</p>
<hr />



<h2 id="privacy">Privacy</h2>
<h3>How do you handle privacy in respect to uploaded hashes?</h3>
<p> </p>

<h3>What about the plaintexts?</h3>
<p>We store these to improve our service. If the plaintext for a hash is found you're urged to replace it as soon as possible. If this system can figure it out, someone else can too.</p>

<h3>But what about the web server logs?</h3>
<p>Request to our webserver have the will be logged with the IP-address. However, the webserver logs are kept in memory only for a couple of hours for the sake of debugging. Just as everybody else, we screw up things on the server side from time to time.</p>

<h3>Anything else?</h3>
<p>Yes, this service is run over secure HTTP (<em>https://</em>) to achieve end-to-end encryption for your privacy. There are simply no good reasons to do plaintext HTTP. Ever.</p>

<h2 id="other">Other</h2>
<h3>Can I buy the whole platform and run this thing myself?</h3>
<p>Yes, please <a href="contact">contact us</a> for more information.</p>

<h3>[&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</h3>
<p style="height: 300px">This space was intentionally left blank.</p>
