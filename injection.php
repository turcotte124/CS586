<?php
/* injection.php: Demonstrates SQL injection and how prepared statements, with their
 * automatic input escaping, avoid it.
 *
 * The PDO database abstraction library used in other files wraps all SQL state-
 * ments in a prepared statement, which prevents this kind of attack. PostgreSQL
 * will not allow prepared statements that contain a semicolon, so the attempt
 * at injecting SQL will fail.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-24
 *
 */

// import the database connection information
require_once 'db-config.php';
$conn = null;

/* connect_to_db(): Attempts to connect to the database by constructing
 * a PDO object. See db-config.php for construction of the connection
 * string / data source name.
 *
 * Returns: A connection to the database.
 */
function connect_to_db () {
	// use these variables outside the function
	global $conn;
	global $raw_connection_string;

	if (!is_null($conn))
		return $conn;

	$conn = pg_connect($raw_connection_string);

	if (!$conn) {
		die('Could not connect to the database.');
	}

	return $conn;
}

/* disconnect_from_db(): Sets the database connection to null so it closes. */
function disconnect_from_db () {
	global $conn;
	pg_close($conn);
	$conn = null;
}

/* Main Code Entry Point */

$inject_text = '500;UPDATE pc SET price = 0 WHERE model = 1001;--';

// collect the form input and set variables
if (!empty($_POST)) {
	$budget = isset($_POST['budget']) ? $_POST['budget'] : '';
	$form_name  = isset($_POST['form_name']) ? $_POST['form_name'] : 'good';
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

			<h2>Search for PCs</h2>

			<h3>Sanitized Input</h3>

			<p>This form sanitizes its input using a prepared statement.</p>

			<form action="injection.php" method="post" class="form-horizontal">
				<input type="hidden" name="form_name" value="good"/>
				<div class="control-group">
					<label for="budgetField1" class="control-label">Budget:</label>
					<div class="controls">
						<input type="text" id="budgetField1" name="budget" value="<?php echo $inject_text; ?>"/>
						<button type="submit" class="btn btn-success">Show Matching PCs</button>
					</div>
				</div>
			</form>

			<h3>Dangerous Input</h3>

			<p>This form simply substitutes its input into the SQL without
				sanitizing it first, allowing for SQL injection.</p>

			<form action="injection.php" method="post" class="form-horizontal">
				<input type="hidden" name="form_name" value="bad"/>
				<div class="control-group">
					<label for="budgetField2" class="control-label">Budget:</label>
					<div class="controls">
						<input type="text" id="budgetField2" name="budget" value="<?php echo $inject_text; ?>"/>
						<button type="submit" class="btn btn-danger">Show Matching PCs</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<hr class="offset1 span10"/>

	<div class="row">
		<div class="offset2 span8">

<?php
	if (!empty($budget)) {
		$conn = connect_to_db();

		$result = null;
		$errors = array();

		if ($form_name == 'bad') {
			print '<div class="alert alert-warn">Unsanitized input will be used.</div>';

			// construct the SQL using string concatenation; note that we 
			// haven't escaped $budget with pg_escape_literal().
			$sql = 'SELECT model, speed, price FROM pc WHERE price <= ' . $budget;

			// attempt to execute the query and get results
			$result = pg_query($conn, $sql);
		} else {
			print '<div class="alert alert-success">Sanitized input will be used.</div>';
			// construct a prepared statement
			$sql = 'SELECT model, speed, price FROM pc WHERE price <= $1';
			$result = pg_prepare($conn, 'pc_price_query', $sql);

			// only query if statement prepared successfully
			if ($result) {
				$result = pg_execute($conn, 'pc_price_query', array($budget));
			}
		}

		if ($result) {
			// print '<p>Query status: ' . pg_result_status($result, PGSQL_STATUS_STRING) . '</p>'; // DEBUG

			if (pg_num_rows($result) > 0) {
				print '<h2>PCs Under $' . $budget . '</h2>';

				print '<table class="table table-striped">';
				print '<tr><th>Model</th><th>Speed (Ghz)</th><th>Price</th></tr>';
				while ($row = pg_fetch_assoc($result)) {
					print '<tr><td>' . $row['model'] . '</td><td>' . $row['speed'] .
					      '</td><td>' . $row['price'] . '</td></tr>';
				}
			} else {
				print '<p>No PCs under $' . $budget . ' were found.</p>';
			}
		} else {
			// pg_query(), pg_prepare(), or pg_execute() returned false.
			// print a description of what went wrong.
			print '<div class="alert alert-error">' . pg_last_error($conn) . '</div>';
		}

		disconnect_from_db();
	} else {
		print '<p>Perform a search using one of the forms above.</p>';
	}
?>
		</div>
	</div>
</body>
</html>
