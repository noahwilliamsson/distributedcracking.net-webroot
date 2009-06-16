#!/bin/sh
set -x
cd temp || exit 1

for pkg in win32-mmx win32-sse2; do
	d="distributedcracking-$pkg";
	mkdir "$d" 2>/dev/null
	cp ../john-1.7.2-webapi/run/*.chr "$d"
	cp ../john-1.7.2-webapi/run/ca.pem "$d"
	cp ../john-1.7.2-webapi/run/john.conf "$d"
	cp ../john-1.7.2-webapi/run/password.lst "$d"
	cp ../john-1.7.2-webapi/doc/LICENSE "$d"

	cp ../john-1.7.2-webapi/executables/"$pkg"/john.exe "$d"
	cp ../john-1.7.2-webapi/executables/*.dll "$d"

	zip -qr "../../software/$d.zip" "$d"
	rm -fr "$d"
done


for pkg in macosx-sse2 ubuntu-x86-64 ubuntu-x86-sse2 gentoo-x86-sse2 rhel4-x86-sse2 debian-x86-sse2; do
	d="distributedcracking-$pkg";
	mkdir "$d" 2>/dev/null
	cp ../john-1.7.2-webapi/run/*.chr "$d"
	cp ../john-1.7.2-webapi/run/ca.pem "$d"
	cp ../john-1.7.2-webapi/run/john.conf "$d"
	cp ../john-1.7.2-webapi/run/password.lst "$d"
	cp ../john-1.7.2-webapi/doc/LICENSE "$d"

	cp ../john-1.7.2-webapi/executables/john-"$pkg" "$d/john"

	zip -qr "../../software/$d.zip" "$d"
	rm -fr "$d"
done
