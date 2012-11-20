<?php
/* db-config.php: Sets global database configuration values. Modeled after Word-
 * Press's wp-config.php file.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-18
 *
 */
define('DB_HOST', 'dbclass.cs.pdx.edu'); // Database server host name
define('DB_SCHEMA', '');                 // Postgres schema where tables found (can leave blank for public)
define('DB_NAME', '');                   // Name of the database to connect to
define('DB_USER', '');                   // Postgres username
define('DB_PASS', '');                   // Postgres password

$db_connection_string = sprintf('pgsql:dbname=%s;host=%s', DB_NAME, DB_HOST);
?>