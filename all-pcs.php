<?php
/* all-pcs.php: Lists all PCs in the database.
 * 
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-25
 *
 */

// import the database connection information
require_once 'db-config.php';
$conn = null;

/* connect_to_db(): Attempts to connect to the databae by constructing
 * a PDO object. See db-config.php for construction of the connection
 * string / data source name.
 *
 * Returns: A connection to the database.
 */
function connect_to_db () {
	// use these variables outside the function
	global $conn;
	global $pdo_connection_string;

	if (!is_null($conn))
		return $conn;

	try {
	    // connect to the database
		$conn = new PDO($pdo_connection_string, DB_USER, DB_PASS);

		// throw exceptions when errors occur
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// fetch rows as associative arrays (dictionaries)
		$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
	  echo "Database exception: " . $e->getMessage() . "\n";
	  die;
	}

	return $conn;
}

/* disconnect_from_db(): Sets the database connection to null so it closes. */
function disconnect_from_db () {
	global $conn;
	$conn = null;
}

/* print_pc_table: Generates an HTML table listing the maker and specs of every PC. */
function print_pc_table () {
	// connect to the database if not done yet
	$conn = connect_to_db();

	// construct the query string
	$sql = 'SELECT maker, pc.model, speed, ram, hd, price FROM product, pc ' .
	       'WHERE product.model = pc.model ORDER BY maker, price DESC';

	// execute the query, getting a PDOStatement object
	$result = $conn->query($sql);

	// if we found any results
	if ($result->rowCount() > 0) {
		print '<table class="table table-striped">';
		print '<tr><th>Maker</th><th>Model #</th><th>Speed</th><th>RAM</th><th>HD</th><th>Price</th></tr>';

		// iterate over each product
		while ($row = $result->fetch()) {
			// print an HTML table row for the current product under the cursor
			print '<tr>';
			foreach ($row as $key => $value) {
				printf('<td>%s</td>', $value);
			}
			print '</tr>';
		}

		print '</table>';

		// reset cursor for next query
		$result->closeCursor();
	} else {
		print '<h4>No PCs found.</h4>';
	}

	disconnect_from_db();
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product Listing</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/custom.css">
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
	<div class="row">
		<?php include 'navigation.php'; ?>

		<div class="offset1 span7">

			<h2>All PCs</h2>

			<?php print_pc_table(); ?>

		</div>
	</div>

</body>
</html>
