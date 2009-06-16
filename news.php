<h1>News</h1>
<h3>Updates to web the GUI
</h3>
<p>
<strong style="font-weight: normal; font-size: 0.9em; color: green">[ September 2008 ]</strong>
<br />
The web GUI has been slightly overhauled and some team-related issues have been squashed.</p>
<p>We also keep track of the number of hashes cracked for your jobs, notifying you on the Jobs-page about the number of recently cracked hashes. Also, while displaying details of a job, there's now a table with at most 20 plaintexts that have been found during the last two weeks.</p>

<h3>Various client software bug fixes</h3>
<p>We've fixed a bunch of issues in the client software. The new versions are available at the <a href="download">download</a> page.</p>

<h3>Rainbowtables queried for raw MD5 hashes</h3>
<p>We now query online rainbowtables as an additional way of finding the plaintext for raw MD5 hashes.<br />
This is done automatically every ten minutes with rechecks of hashes every week.</p>

<?php if($invite_only) { ?>
<h3>Invite only</h3>
<p>More new users have put an unexpected load on this poor server!<br />
We have decided to make the service <a href="frequently-asked-questions#invite">invite only</a>. For now.</p>
<?php } ?>

<h3>Windows GUI</h3>
<p>We now have a friendlier user interface for Windows users. An icon in the system tray allows for finding out what the client is currently working on.  Here's a screenshot of the client software running in Window XP.<br />
<img src="img/dc-windows.png" alt="" />
</p>

<h3>TODO</h3>
<p>We're working hard to keep up with your feature requests. Here's our current TODO list<br />
<ul>
<li>Restoring the client software's SMP support</li>
<li>Versioned wordlist attacks (<span style="color:red">almost there, disabled due to bandwidth issues</span>)</li>
<li>Restoring an existing John the Ripper session (john.rec)</li>
<li>Improving the web UI (<span style="color:red">working on it</span>)</li>
<li>Statistics, statistics, statistics!</li>
<li>Optional email notifications and reports</li>
</ul>
