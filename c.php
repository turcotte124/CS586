<?php
/* c.php: Implements Exercise 9.3.1 (c) from "Database Systems: The Complete Book",
 * 2nd ed. by Garcia-Mollina, Ullman, and Widom.
 *
 * Given a manufacturer name, it lists the specifications of all the products 
 * manufactured by that manufacturer.
 * 
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-24
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

/* print_product_table: Generates an HTML table listing the specs of items
 * of type $product_type manufactured by $maker_name.
 *
 * Params:
 *  - $maker_name:   A manufacturer name from the product table.
 *  - $product_type: One of 'pc', 'laptop', or 'printer'.
 *  - $column_names: The columns from the table $product_type we want printed.
 */
function print_product_table ($maker_name, $product_type, $column_names = array()) {
	// connect to the database if not done yet
	$conn = connect_to_db();

	// sanitize the input
	$clean_maker_name = $conn->quote($maker_name);

	// if column names empty, select all by default
	if (count($column_names) == 0) {
		$project_list = '*';
	} else {	
		// otherwise build project list: prepend disambiguation name to each colunn
		// name, then join them with ','
		$project_list = implode(',', array_map(function ($value) { return 'type.' . $value; }, $column_names));
	}

	// construct the query string; this cannot be done using a prepared statement
	// because the table name is dynamic
	$sql = sprintf('SELECT %s FROM product, %s as type WHERE maker = %s AND product.model = type.model', 
		           $project_list, $product_type, $clean_maker_name);

	// execute the query, getting a PDOStatement object
	$products = $conn->query($sql);

	// special-case capitalization of PC
	$product_type = $product_type == 'pc' ? strtoupper($product_type) : $product_type;

	$header_printed = false;

	// if we found any results
	if ($products->rowCount() > 0) {
		printf('<h4>%ss from %s</h4>', ucfirst($product_type), $maker_name);
		print '<table class="table table-striped">';

		// iterate over each product
		while ($row = $products->fetch()) {
			// print a header line with the column names if this is the first row
			if (!$header_printed) {
				printf('<tr><th>%s</th></tr>', implode('</th><th>', array_keys($row)));
				$header_printed = true;
			}

			// print an HTML table row for the current product under the cursor
			print '<tr>';
			foreach ($row as $key => $value) {
				printf('<td>%s</td>', $value);
			}
			print '</tr>';
		}

		print '</table>';

		// reset cursor for next query
		$products->closeCursor();
	} else {
		printf('<h4>No %ss found for %s</h4>', $product_type, $maker_name);
	}
}

/* Main Code Entry Point */

// get maker from $POST array if the form was submitted
if (isset($_POST['maker'])) {
	$maker_name = $_POST['maker'];
} else {
	$maker_name = null;
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
		<div class="offset2 span8">

			<h2>Get All Products By a Manufacturer</h2>

			<form action="c.php" method="post" class="form-horizontal">
				<div class="control-group">
					<label for="makerMenu" class="control-label">Select a manufacturer:</label>
					<div class="controls"><select name="maker" id="makerMenu">
<?php
					// build the list of manufacturers dynamically from the database
					$conn = connect_to_db();
					$makers = $conn->query('SELECT DISTINCT maker FROM product ORDER BY maker');
					foreach ($makers as $row) {
						// re-select the maker the user picked
						$selected = $row['maker'] == $maker_name ? 'selected="selected"' : '';
						printf('<option value="%s" %s>%s</option>', $row['maker'], $selected, $row['maker']);
					}
					$makers->closeCursor();
?>			
						</select>
						<button type="submit" class="btn btn-primary">Show Products</button>
					</div>
					</div>
			</form>
		</div>
	</div>

	<hr class="offset1 span10"/>

	<div class="row">
		<div class="offset2 span8">

<?php
	if (isset($maker_name)) {
		printf('<h3>Products From %s</h3>', $maker_name);

		print '<div class="offset1">';
		print_product_table($maker_name, 'pc', array('model', 'speed', 'ram', 'hd', 'price'));
		print '</div>';

		print '<div class="offset1">';
		print_product_table($maker_name, 'laptop', array('model', 'speed', 'ram', 'screen', 'hd', 'price'));
		print '</div>';

		print '<div class="offset1">';
		print_product_table($maker_name, 'printer', array('model', 'color', 'type', 'price'));
		print '</div>';
	} else {
		echo '<p>Please select a manufacturer from the form above to see their products.</p>';
	}

	disconnect_from_db();
?>
		</div>
	</div>
</body>
</html>
