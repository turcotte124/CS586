<?php
/* e.php: Implements Exercise 9.3.1 (e). (FIXME book info). Asks the user for a
 * model number, speed, amount of RAM, hard disk size, and price for a new PC.
 * Checks if a PC with the given model number exists, and if not it inserts a
 * new PC with the given properties. Gives an error if the model number already
 * exists.
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

	// set up manufacturer pull-down list
		// select distinct maker from product order by maker
		// how to handle new maker?

	// get a bunch of stuff from the post array

	// start a transaction

	// try to find a product with the given model number
	// select count(model) from product where model = $model_num;

	// if count is not zero, abort the transaction, report an error
		// store error message in session
		// repopulate form with POST data

	// if count is zero
		// insert data into the product table
		// insert into product (maker, model, type) values ($maker, $model_num, $type)
		// fixme: catch exception?

		// insert data into the pc table
		// insert into pc (model, speed, ram, hd, price) values ($model_num, $speed, $ram, $hd, $price)
		// fixme: catch exception?

		// commit transaction

		// store success message in session
		// go back to form

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
  <title>Add a New PC</title>
  <!-- link rel="stylesheet" href="css/styles.css?v=1.0" -->
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
</body>
</html>