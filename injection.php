<?php
/* injection.php: Demonstrates SQL injection.
 *
 * The PDO database abstraction library used in other files wraps all SQL state-
 * ments in a prepared statement, which prevents this kind of attack. PostgreSQL
 * will not allow prepared statements that contain a semicolon, so the attempt
 * at injecting SQL will fail.
 *
 * The following code uses the raw pg_* database driver functions to demonstrate
 * the danger of not validating your application's input. The unsecure version of
 * the form constructs its SQL query using string concatenation without doing any
 * type validation or other checks for SQL injection, whereas the secure version
 * of the form uses pg_send_query_params() to insure that types are checked and
 * the SQL contains only a single SQL statement.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-27
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

// Collect the form input and set variables.
if (!empty($_POST)) {
	$budget = isset($_POST['budget']) ? $_POST['budget'] : '';

	// Limits the demo so users can't do random damage to my database.
	// The bugdet must either be numeric or the expected injection.
	if (!is_numeric($budget) && $budget != $inject_text) {
		$budget = $inject_text;
	}

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
		<?php include 'navigation.php'; ?>
		<div class="offset2 span8">

			<h2>Search for PCs</h2>

			<h3>Sanitized Input</h3>

			<p>This form prevents SQL injection by constructing its query in a
				way that insures that the types of its parameters are correct.</p>

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
				validating it first, allowing for SQL injection.</p>

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

			<p><a href="reset-injection.php">Reset Injection Demo</a></p>
		</div>
	</div>

	<hr class="offset2 span10"/>

	<div class="row">
		<div class="offset3 span8">

<?php
	if (!empty($budget)) {
		$conn = connect_to_db();

		$result = null;
		$errors = array();

		// Vary how we construct the query based on the form used.
		if ($form_name == 'bad') {
			print '<div class="alert alert-warn">Unsanitized input will be used.</div>';

			// Construct the SQL using string concatenation; note that we 
			// haven't validated that $budget is an integer or restricted the
			// query to a single statement in any way.
			$sql = 'SELECT model, speed, price FROM pc WHERE price <= ' . $budget;

			// attempt to execute the query and get results
			if (pg_send_query($conn, $sql)) {
				$result = pg_get_result($conn);
			} else {
				$errors[] = 'Could not dispatch query request.';
			}
		} else {
			print '<div class="alert alert-success">Sanitized input will be used.</div>';

			// $1 here is a placeholder for the prepared statement's parameter
			$sql = 'SELECT model, speed, price FROM pc WHERE price <= $1';

			// Unlike pg_send_query(), pg_send_query_params() makes sure that its
			// parameters are the right type and that the query contains only one
			// SQL statement (i.e. has no semicolons).
			//
			// Using a prepared statement with pg_send_prepare() and pg_send_execute()
			// would have the same effect.
			if (pg_send_query_params($conn, $sql, array($budget))) {
				$result = pg_get_result($conn);
			} else {
				$errors[] = 'Could not dispatch query request.';
			}
		}

		if ($result && !pg_result_error($result)) {
			// The query was successful. Print out the result set.
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
			// pg_send_query() returned false, or pg_result_error() was true.
			// Print out the errors.
			print '<div class="alert alert-error">';
			print '<h3>There were some problems with your query:</h3>';

			// Deal with the case where pg_send_query() failed.
			foreach ($errors as $error_str) {
				print '<p>' . $error_str . '</p>';
			}

			// Deal with the case where the query was unsuccessful.
			if ($result && pg_result_error($result)) {
				print '<p>' . pg_result_error($result) . '</p>';
			}

			print '</div>';
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
