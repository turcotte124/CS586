<?php
/* c.php: Implements Exercise 9.3.1 (c). Given a manufacturer name, it lists
 * the specifications of all the products manufactured by that manufacturer.
 * 
 * FIXME: Book information
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-19
 *
 */

// import the database connection information
require_once 'db-config.php';
$conn = null;

function print_product_table ($maker_name, $product_type, $column_names = array()) {
	// connect to the database if not done yet
	$conn = connect_to_db();

	// sanitize the input
	$clean_maker_name = $conn->quote($maker_name);

	// if column names empty, select all by default
	if (count($column_names) == 0) {
		$project_list = '*';
	} else {	
		// otherwise build project list
		$project_list = implode(',', array_map(function ($value) { return 'type.' . $value; }, $column_names));
	}

	// construct the query string
	$sql = sprintf('SELECT %s FROM product, %s as type WHERE maker = %s AND product.model = type.model', 
		           $project_list, $product_type, $clean_maker_name);

	// fetch each row as a dictionary indexed by column name
	$products = $conn->query($sql, PDO::FETCH_ASSOC);

	$header_printed = false;

	// if results found
	if ($products->rowCount() > 0) {
		echo sprintf('<h3>%ss from %s:</h3>', $product_type, $maker_name);
		print '<table>';

		// iterate over each product
		while ($row = $products->fetch()) {
			// print a header from the array keys if this is the first row
			if (!$header_printed) {
				echo sprintf('<tr><th>%s</th></tr>', implode('</th><th>', array_keys($row)));
				$header_printed = true;
			}

			// print an HTML table row for this product
			print '<tr>';
			foreach ($row as $key => $value) {
				echo sprintf('<td>%s</td>', $value);
			}
			print '</tr>';
		}

		print '</table>';

		// reset cursor for next query
		$products->closeCursor();
	} else {
		echo sprintf('<h3>No products of type %s found for %s.</h3>', $product_type, $maker_name);
	}
}

function connect_to_db () {
	global $conn;
	global $db_connection_string;

	if (!is_null($conn))
		return $conn;

	try {
	    // connect to the database
		$conn = new PDO($db_connection_string, DB_USER, DB_PASS);
	} catch (PDOException $e) {
	  echo "Database exception: " . $e->getMessage() . "\n";
	  die;
	}

	return $conn;
}

function disconnect_from_db () {
	global $conn;
	$conn = null;
}

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
  <!-- link rel="stylesheet" href="css/styles.css?v=1.0" -->
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
	<form action="c.php" method="post">
		Select a manufacturer:
		<select name="maker">
<?php
		$conn = connect_to_db();
		$makers = $conn->query('SELECT DISTINCT maker FROM product ORDER BY maker');
		foreach ($makers as $row) {
			$selected = $row['maker'] == $maker_name ? 'selected="selected"' : '';
			echo sprintf('<option value="%s" %s>%s</option>\n', $row['maker'], $selected, $row['maker']);
		}
		$makers->closeCursor();
?>			
		</select>
		<input type="submit" value="Show Products"/>
	</form>

	<hr/>

<?php
	if (isset($maker_name)) {
		echo sprintf('<h2>Products From %s:</h2>', $maker_name);
		print_product_table($maker_name, 'pc', array('model', 'speed', 'ram', 'hd', 'price'));
		print_product_table($maker_name, 'laptop', array('model', 'speed', 'ram', 'screen', 'hd', 'price'));
		print_product_table($maker_name, 'printer', array('model', 'color', 'type', 'price'));
	} else {
		echo '<p>Please select a manufacturer from the form above to see their products.</p>';
	}

	disconnect_from_db();
?>

</body>
</html>
