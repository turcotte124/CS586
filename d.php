<?php
/* d.php: Implements Exercise 9.3.1 (d). (FIXME book info). Given a budget and
 * minimum CPU speed from the user, finds the least expensive combination of PC
 * and printer meeting within the budget and with at least the given CPU speed,
 * going with a color printer if possible. Prints the model numbers of the pro-
 * ducts selected.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-20
 *
 */

// import the database connection information
require_once 'db-config.php';
$conn = null;

function find_system ($min_speed, $budget) {
	$conn = connect_to_db();

	// construct the query string
	$sql = 'SELECT pc.model AS cmodel, printer.model AS pmodel, printer.color AS color, ' .
	       'pc.price + printer.price AS total FROM pc, printer ' .
	       'WHERE pc.speed >= :min_speed AND pc.price + printer.price <= :budget_amt ' .
	       'ORDER BY total';

	// create a prepared statement
	$stmt = $conn->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	// set the prepared statement's variables; these are escaped automatically
	$stmt->bindValue(':min_speed', $min_speed);
	$stmt->bindValue(':budget_amt', $budget);

	// ready the query result and cursor
	$stmt->execute();
	return $stmt;
}

function print_pc_details ($pc_model) {
	$conn = connect_to_db();

	$sql = 'SELECT maker, pc.model AS model, speed, ram, hd, price ' .
	       'FROM product, pc WHERE pc.model = :model_num AND '. 
	       'product.model = pc.model';

	$stmt = $conn->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);

	//$stmt->bindValue(':model_num', $pc_model);
	$stmt->execute(array(':model_num' => $pc_model));

	$row = $stmt->fetch();

	$row_text = '';
	foreach ($row as $key => $val) {
		$row_text .= '<td>' . $val . '</td>';
	}

	$stmt->closeCursor();
?>
	<table class="table">
		<tr><th>Maker</th><th>Model #</th><th>CPU Ghz</th><th>RAM</th><th>HD</th><th>Price</th></tr>
		<tr><?php echo $row_text; ?></tr>
	</table>
<?php
}

function print_printer_details ($printer_model) {
	$conn = connect_to_db();

	$sql = 'SELECT maker, printer.model AS model, color, printer.type AS type, price ' .
	       'FROM product, printer WHERE printer.model = :model_num AND ' .
	       'product.model = printer.model';

	$stmt = $conn->prepare($sql);

	$stmt->execute(array(':model_num' => $printer_model));
	$row = $stmt->fetch();

	$row_text = '';
	foreach ($row as $key => $val) {
		$row_text .= '<td>' . $val . '</td>';
	}

	$stmt->closeCursor();
?>
	<table class="table">
		<tr><th>Maker</th><th>Model #</th><th>Color</th><th>Type</th><th>Price</th></tr>
		<tr><?php echo $row_text; ?></tr>
	</table>
<?php
}

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

function disconnect_from_db () {
	global $conn;
	$conn = null;
}

/* Entry Point */

// FIXME: Describe what this does

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
	if (!empty($budget) && !empty($min_speed)) {
		$systems = find_system($min_speed, $budget);

		if ($systems->rowCount() > 0) {
			$best_found = null;

			while ($row = $systems->fetch()) {
				// initialize the best option with the first system found
				if (is_null($best_found)) {
					$best_found = $row;
				}

				// we prefer a color printer over black and white, even
				// if it's more expensive, so search for first color printer
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
