= Implementation =

With the exception of injection.php, all of the PHP scripts use the PHP Data
Objects (PDO) abstraction layer for database access. Because PDO prevents the
SQL injection attack constructed in injection.php, that file uses PHP's
PostgreSQL database extension functions.

PDO Documentation: http://www.php.net/manual/en/book.pdo.php
PHP PostgreSQL Database Extentions: http://www.php.net/manual/en/book.pgsql.php

= Files Included =

 Readme.txt: This file

 all-pcs.php: Lists the specifications of all the PCs in the database as an
  HTML table.

 css/: Cascading style sheet files for the application. These files are from
  Twitter's Bootstrap framework: http://twitter.github.com/bootstrap/index.html

 c.php: Lists all the products (PCs, laptops, and printers) made by a given
  manufacturer. Implements Exercise 9.3.1 (c) from the textbook.

 d.php: Finds a combination of PC and printer, given a minimum acceptable
  CPU speed and a total budget. Implements Exercise 9.3.1 (d) from the textbook.

 db-config.php: Holds database configuration information. To configure your
  database, set the constants DB_HOST, DB_NAME, DB_USER, and DB_PASS. The file
  sees to the creation of connection strings for PDO ($pdo_connection_string)
  and raw Postgres access ($raw_connection_string).

 e.php: Given the full specs of a PC (maker, model, CPU speed, etc.), inserts a
  new PC into the database if the model number is unique, otherwise prints an
  error. Demonstrates how to manually do transactions using the PDO library.
  Implements Exercise 9.3.1 (e) from the textbook.

 img/: Image files from Twitter Bootstrap.

 injection.php: Demonstrates an SQL injection attack. The PDO library takes
  measures to prevent this, so this is implemented using PHP's PostgreSQL
  database extensions.

 navigation.php: Shared navigation sidebar code included into other pages.

 pcs-dump.sql: A dump of the database schema and data used.

= Setting Up Web Access =

The CAT provides web hosting for CS students. The server supports code written
in PHP, so you can use your CAT web hosting to test these files or ones you
derive from them.

Follow the instructions at the following pages to set up web hosting:

http://cat.pdx.edu/web.html
http://cat.pdx.edu/web/creating-web-pages.html

After you have set up web hosting, edit db-config.php to reflect the configuration
of your database, then upload the files to your public_html directory.

For example, the following shows the process of uploading files to the web
for user "harrel2":

heathharrelson:CS586 $ sftp harrel2@web.cecs.pdx.edu
Connected to web.cecs.pdx.edu.
sftp> ls
cs 201         maildir        public_html    smb_files      
sftp> cd public_html
sftp> mput *.php
sftp> ls -l
-rw-r--r--    1 harrel2  them         2771 Nov 25 18:56 all-pcs.php
-rw-r--r--    1 harrel2  them         5930 Nov 25 18:15 c.php
drwxr-xr-x    2 harrel2  them            5 Nov 21 22:37 css
-rw-r--r--    1 harrel2  them         8549 Nov 25 18:18 d.php
-rw-r--r--    1 harrel2  them          697 Nov 24 14:41 db-config.php
-rw-r--r--    1 harrel2  them         9568 Nov 25 18:19 e.php
drwxr-xr-x    2 harrel2  them            4 Nov 21 14:45 img
-rw-r--r--    1 harrel2  them         6619 Nov 25 19:12 injection.php
-rw-r--r--    1 harrel2  them          514 Nov 25 18:58 navigation.php
sftp> 

Note that all the files have the "world read" bit set (the last "r" in the
permission string "-rw-r--r--"). The PHP files need this bit set to be 
read and executed by the web server process.

If a page is not readable by the web server, your browser will just receive
a blank page.
