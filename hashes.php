	<h1>Information about hashes</h1>
	<p>Hashes are one-way functions that turns data, <em>the key</em>, into gibberish of a certain length. The original key cannot be derieved from the hash.</p>
	<p>The only way guess what the key once was is to run the same hash function over different input until you get a matching hash. This technique is known as brute forcing.</p>

	<h2 id="des">DES</h2>
	<p>DES is short for Data Encryption Standard and is a hash function with a 56-bit key. </p>
	<p>UNIX systems traditionally used DES to encrypt users' passwords which were stored in a world readable file called <em>/etc/passwd</em>. It suffers from problems such as limiting input to 8 characters and being relatively quick to crack.</p>
	<p>A DES hash is recognized by having a length of 13 characters, each being in the range of a-z, A-Z, 0-9, . and /<br >Example: <em>H.4x/AaVxisu2</em></p>

	<h2 id="freebsdmd5">FreeBSD MD5</h2>
	<p>FreeBSD MD5 is a salted MD5 hash invented by FreeBSD as a replacement for the less secure DES hash. The hash function is about 200-400 times slower compared to DES. The hash function also doesn't suffer from UNIX DES' 8 character limitation.<br />It's commonly used on FreeBSD, Linux and Solaris systems today.</p>
	<p>A FreeBSD MD5 hash is recognized by the <em>$1$</em> prefix, with a third <em>$</em> splitting the salt and the hash apart. It's 34 characters long.<br />Example: <em>$1$.fe19y73$b7YakTXXb4.ISjDTdRo5R0</em></p>

	<h2 id="rawmd5">Raw MD5</h2>
	<p>A raw MD5 hash is 32 characters long and is the hexadecimal representation of a 16 byte hash. It's commonly used in web software where passwords aren't stored in plaintext. The characters used are the hexadecimal, ranging from 0-9 and a-f.<br />Example: <em>00bfc8c729f5d4d529a412b12c58ddd2</em></p>

	<h2>More hashes</h2>
	<p>DistributedCracking.net supports a whole lot of other hashes. Below is a list of those that are currently enabled.
		<ul>
			<li><strong>DES</strong> <em>(old style UNIX /etc/passwd)</em></li>
			<li><strong>FreeBSD MD5</strong> <em>(found in FreeBSD's /etc/master.passwd or Linux /etc/passwd)</em></li>
			<li><strong>OpenBSD Blowfish</strong></li>
			<li><strong>Raw MD5</strong> <em>(common in web services)</em></li>
			<li><strong>Raw SHA-1</strong> <em>(sometimes used for storing creditcard numbers)</em></li>
			<li><strong>MySQL v3.23</strong> (16 byte hashes)</li>
			<li><strong>MySQL v4.1</strong> (41 byte hashes)</li>
		</ul>
	</p>
