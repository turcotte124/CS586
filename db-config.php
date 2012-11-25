<?php
/* db-config.php: Sets global database configuration values. Modeled after Word-
 * Press's wp-config.php file.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-24
 *
 */
define('DB_HOST', 'dbclass.cs.pdx.edu'); // Database server host name
define('DB_NAME', '');                   // Name of the database to connect to
define('DB_USER', '');                   // Postgres username
define('DB_PASS', '');                   // Postgres password

$pdo_connection_string = sprintf('pgsql:dbname=%s;host=%s', DB_NAME, DB_HOST);
$raw_connection_string = sprintf('dbname=%s host=%s user=%s password=%s', 
	                             DB_NAME, DB_HOST, DB_USER, DB_PASS);
?>