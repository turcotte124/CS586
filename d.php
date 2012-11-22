<?php
/* d.php: Implements Exercise 9.3.1 (d) from "Database Systems: The Complete Book",
 * 2nd ed. by Garcia-Mollina, Ullman, and Widom.
 *
 * Given a budget and minimum CPU speed from the user, finds the least expensive
 * combination of PC and printer within the budget and with at least the given CPU
 * speed, selecting a color printer if possible. Prints full details of the 
 * products selected.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-20
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
	global $conn;
	global $db_connection_string;

	if (!is_null($conn))
		return $conn;

	try {
	    // connect to the database
		$conn = new PDO($db_connection_string, DB_USER, DB_PASS);

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

/* find_system(): Find the combinations of computer and printer that satisfy
 * the user's minimum speed requirement and are within budget.
 *
 * Params:
 *  - $min_speed: The user's minimum CPU speed in Ghz (a float).
 *  - $budget: The amount the user is willing to spend.
 *
 * Retuns: A PDOStatement query result.
 */
function find_system ($min_speed, $budget) {
	$conn = connect_to_db();

	// construct the query string: finds all pairs of pc and printer that meet the
	// user's criteria, in ascending order by price
	$sql = 'SELECT pc.model AS cmodel, printer.model AS pmodel, printer.color AS color, ' .
	       'pc.price + printer.price AS total FROM pc, printer ' .
	       'WHERE pc.speed >= :min_speed AND pc.price + printer.price <= :budget_amt ' .
	       'ORDER BY total';

	// create a prepared statement for the query
	$stmt = $conn->prepare($sql);

	// set the prepared statement's variables; these are escaped automatically
	$stmt->bindValue(':min_speed', $min_speed);
	$stmt->bindValue(':budget_amt', $budget);

	// ready the query result and cursor
	$stmt->execute();
	return $stmt;
}

/* print_pc_details(): Outputs an HTML table with the full specifications of
 * the PC model found when the user searched for a PC / printer system.
 *
 * Params:
 *  - $pc_model: The model number of the PC.
 */
function print_pc_details ($pc_model) {
	$conn = connect_to_db();

	// query text: will get the manufacturer and full specs of the selected PC
	// :model_num is a parameter that must be set before execution
	$sql = 'SELECT maker, pc.model AS model, speed, ram, hd, price ' .
	       'FROM product, pc WHERE pc.model = :model_num AND '. 
	       'product.model = pc.model';

	// create a prepared statement
	$stmt = $conn->prepare($sql);

	// execute the query on the database
	$stmt->execute(array(':model_num' => $pc_model));

	// construct a table row from the result
	$row = $stmt->fetch();
	$row_text = '';
	foreach ($row as $key => $val) {
		$row_text .= '<td>' . $val . '</td>';
	}

	// free resources associated with the cursor so the statement can be called again
	$stmt->closeCursor();

	// the following actually prints the HTML table
?>
	<table class="table">
		<tr><th>Maker</th><th>Model #</th><th>CPU Ghz</th><th>RAM</th><th>HD</th><th>Price</th></tr>
		<tr><?php echo $row_text; ?></tr>
	</table>
<?php
}

/* print_printer_details(): Outputs an HTML table with the full specifications
 * of the printer found when the user searched for a PC / printer system.
 *
 * Params:
 *  - $printer_model: The model number of the printer.
 */
function print_printer_details ($printer_model) {
	$conn = connect_to_db();

	// query text: will get the manufacturer and full specs of the selected PC
	// :model_num is a parameter that must be set before execution
	$sql = 'SELECT maker, printer.model AS model, color, printer.type AS type, price ' .
	       'FROM product, printer WHERE printer.model = :model_num AND ' .
	       'product.model = printer.model';

	// create a prepared statement
	$stmt = $conn->prepare($sql);

	// execute the prepared statement on the database
	$stmt->execute(array(':model_num' => $printer_model));


	// construct the table row from the database result
	$row = $stmt->fetch();
	$row_text = '';
	foreach ($row as $key => $val) {
		$row_text .= '<td>' . $val . '</td>';
	}

	// free resources associated with the cursor so the statement can be called again
	$stmt->closeCursor();

	// the following actually prints out the HTML table
?>
	<table class="table">
		<tr><th>Maker</th><th>Model #</th><th>Color</th><th>Type</th><th>Price</th></tr>
		<tr><?php echo $row_text; ?></tr>
	</table>
<?php
}

/* Main Code Entry Point */

// If the user submitted the form, get their input from the environment

if (isset($_POST['budget'])) {
	$budget = $_POST['budget'];
	// FIXME check is_numeric()
} else {
	$budget = "";
}

if (isset($_POST['minspeed'])) {
	$min_speed = $_POST['minspeed'];
	// FIXME check is_numeric()
} else {
	$min_speed = "";
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Find a System That Fits Your Budget</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
	<div class="row">
		<div class="offset2 span8">
			<h2>Find the Best System For Your Budget</h2>

			<form action="d.php" method="post" class="form-horizontal">
				<div class="control-group">
					<label for="speedField" class="control-label">Minumum CPU Speed</label>
					<div class="controls">
						<input type="text" id="speedField" name="minspeed" value="<?php echo $min_speed; ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label for="budgetField" class="control-label">Budget</label>
					<div class="controls">
						<input type="text" id="budgetField" name="budget" value="<?php echo $budget; ?>"/>
					</div>
				</div>

				<div class="controls">
					<button type="submit" class="btn btn-primary">Find System</button>
				</div>
			</form>
		</div>
	</div>

	<hr class="offset1 span10"/>

	<div class="row">
		<div class="offset2 span8">

<?php
	// search for a PC / printer combo if the user submitted the form
	if (!empty($budget) && !empty($min_speed)) {
		$systems = find_system($min_speed, $budget);

		// a printer / PC system meeting their criteria was found
		if ($systems->rowCount() > 0) {
			$best_found = null;

			// find the best system in the results, which is the least expensive
			// system in budget with a color printer, or the least expensive
			// system in budget with a black and white printer if no color printer
			// was available
			while ($row = $systems->fetch()) {
				// initialize the best option with the first system found
				if (is_null($best_found)) {
					$best_found = $row;
				}

				// look until we find a color printer or run out of results
				if ($row['color'] && !$best_found['color']) {
					$best_found = $row;
					break;
				}
			}

			print '<h3>Your Best System: $' . $best_found['total'] . '</h3>';
			print '<div class="offset1">';

			print '<h4>PC Details</h4>';
			print_pc_details($best_found['cmodel']);

			print '<h4>Printer Details</h4>';
			print_printer_details($best_found['pmodel']);
			print '</div>';
		} else {
			print '<p>No systems were found matching your criteria.</p>';
		}

		$systems->closeCursor();
	}

	disconnect_from_db();
?>
		</div>
	</div>
</body>
</html>
