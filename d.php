<?php
/* d.php: Implements Exercise 9.3.1 (d). (FIXME book info). Given a budget and
 * minimum CPU speed from the user, finds the least expensive combination of PC
 * and printer meeting within the budget and with at least the given CPU speed,
 * going with a color printer if possible. Prints the model numbers of the pro-
 * ducts selected.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-18
 *
 */

// import the database connection information
require_once 'db-config.php';

try {
    // connect to the database
	$dbh = new PDO($db_connection_string, DB_USER, DB_PASS);

	$result = $dbh->query('SELECT * FROM product');
	echo "Query found " . $result->rowCount() . " rows.\n";

	// select pc.model, printer.model pc.price + printer.price as total
	// from pc, printer -- cross product
	// where pc.speed > min_speed and pc.price + printer.price < budget_amt
	// order by total

	// find cheapest with color printer (if any)

	// get pc, printer manufacturers (do a join query to get the makers)

	// print out overall description

	// print out PC stats

	// print out printer stats

    // closes the connection
	$dbh = null;
} catch (PDOException $e) {
  echo "Database exception: " . $e->getMessage() . "\n";
  die;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Find a System That Fits Your Budget</title>
  <!-- link rel="stylesheet" href="css/styles.css?v=1.0" -->
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
</body>
</html>