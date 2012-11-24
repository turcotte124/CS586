<?php
/* injection.php: Demonstrates SQL injection and how prepared statements, with their
 * automatic input escaping, avoid it.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-23
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

		try {
			$stmt = null;

			if ($form_name == 'bad') {
				print '<div class="alert alert-warn">Unsanitized input will be used.</div>';

				// construct the SQL using string concatenation; note that we 
				// haven't escaped $budget with $conn->quote()
				$sql = 'SELECT model, speed, price FROM pc WHERE price <= ' . $budget;

				// execute the query and get results
				$stmt = $conn->query($sql);
			} else {
				print '<div class="alert alert-success">Sanitized input will be used.</div>';
				// construct a prepared statement
				$stmt = $conn->prepare('SELECT model, speed, price FROM pc WHERE price <= :budget');

				// bind the statement parameters, which automatically escapes the input
				$stmt->bindParam(':budget', $budget);

				// execute the query and get results
				$stmt->execute();
			}

			if ($stmt->rowCount() > 0) {
				print '<h2>PCs Under $' . $budget . '</h2>';

				print '<table class="table table-striped">';
				print '<tr><th>Model</th><th>Speed (Ghz)</th><th>Price</th></tr>';
				while ($row = $stmt->fetch()) {
					print '<tr><td>' . $row['model'] . '</td><td>' . $row['speed'] .
					      '</td><td>' . $row['price'] . '</td></tr>';
				}
			} else {
				print '<p>No PCs under $' . $budget . ' were found.</p>';
			}
		} catch (PDOException $e) {
			print '<div class="alert alert-error">' . $e->getMessage() . '</div>';
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
