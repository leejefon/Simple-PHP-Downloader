Simple-PHP-Downloader
=====================

Example Usage:
--------------

1. Extract the files in /downloader
2. Update .htaccess accordingly
3. Enter paths of files to download in Download.php


Options:
--------
auth => auth name in $auth
zip* => true/false

* Files can be downloaded zipped by appending .zip to the end of download path as well.


Auth:
-----
realm => Display name for auth popup
type => database/mysql
dbname => Database name
table => Table name
user => User login
pass => User password
hash => md5, sha1, or whatever hash function used to encrypt the password.
salt => Salt used in password.  Right now it only prepends to password.  If salt is attached differently, modify it in Downloader->authenticate function.