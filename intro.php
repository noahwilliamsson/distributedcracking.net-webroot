<h1>Introduction to distributed cracking</h1>
<ul>
	<li><a href="#how-it-works">How it works</a></li>
	<li><a href="#distributed-cracking">Distributed cracking</a></li>
</ul>

<h2 id="how-it-works">How it works</h2>
<p>The idea is pretty simple at a glance.<br />
You create an account with us and <a href="jobs#create">create a job</a> (a collection of hashes). We do some basic analysis of the input, import it into our database and a do some server side computation to split up the work needed in smaller parts, called packets.</p>

<p>You continue to <a href="download">download</a> the client and register the computer as a <a href="node">node</a> of yours using the name and password for your account.</p>

<p>The client will talk to our servers, receive a job to work on and some packet information. The packet essentially tells the client how, and for how long, it should work on the job received.<br />

<p>Found plaintexts are reported to our servers, on which they are queued for later verification before being made available to you.</p>

<h2 id="distributed-cracking">Distributed Cracking</h2>
<p>To be written..  </p>
