<?php
/* e.php: Implements Exercise 9.3.1 (e) from "Database Systems: The Complete Book",
 * 2nd ed. by Garcia-Mollina, Ullman, and Widom.
 *
 * Asks the user for a product model number, speed, amount of RAM, hard disk size,
 * and price for a new PC. Checks if a PC with the given model number exists, 
 * and if not it inserts a new PC with the given properties. Gives an error if 
 * the model number already exists.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-21
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

/* get_input_array(): Gathers input from the $_POST array, setting any
 * unset keys to the empty string.
 *
 * Returns: An array with the keys necessary to insert a new PC.
 */
function get_input_array () {
	$input = array();

	// collect input values from the form
	$input['maker'] = isset($_POST['maker']) ? $_POST['maker'] : '';
	$input['model'] = isset($_POST['model']) ? $_POST['model'] : '';
	$input['speed'] = isset($_POST['speed']) ? $_POST['speed'] : '';
	$input['ram']   = isset($_POST['ram']) ? $_POST['ram'] : '';
	$input['hd']    = isset($_POST['hd']) ? $_POST['hd'] : '';
	$input['price'] = isset($_POST['price']) ? $_POST['price'] : '';

	// use the new_maker field instead of the maker field if it's set
	$new_maker = isset($_POST['new_maker']) ? $_POST['new_maker'] : '';
	if (!empty($new_maker)) {
		$input['maker'] = $new_maker;
	}

	return $input;
}

/* validate_input(): Verifies that all of the values needed to insert a
 * new PC into the datbase have values, and that the values are of the
 * expected types.
 *
 * Params:
 *  - $input_array: An array produced by get_input_array().
 *
 * Returns: An array of errors found in the input. Empty if none found.
 */
function validate_input ($input_array) {
	$errors = array();

	$integer_fields = array('model', 'ram', 'hd', 'price');
	foreach ($integer_fields as $key) {
		if (!is_numeric($input_array[$key]) || preg_match('/\./', $input_array[$key])) {
			$errors[] = "The value of $key must be an integer.";
		}
	}

	if (!is_numeric($input_array['speed'])) {
		$errors[] = "The value of speed must be a number.";
	}

	return $errors;
}

/* insert_new_pc(): Inserts the particulars of a new PC into the product and
 * pc tables.
 *
 * Aborts unless a unique model number is provided.
 *
 * Params:
 *  - $pc_details: A array produced by get_input_array().
 *
 * Returns: An array with the keys 'success' and 'errors', where the value
 * associated with 'success' is a boolean and the value associated with errors
 * is empty on success and an array of strings if the transaction failed.
 */
function insert_new_pc ($pc_details) {
	$conn = connect_to_db();

	$submit_status = array();

	try {
		$conn->beginTransaction();

		// try to find a product with the given model number
		$model_stmt = $conn->prepare("SELECT * FROM product WHERE model = :model_num");
		$model_stmt->bindParam(':model_num', $pc_details['model']);
		$model_stmt->execute();

		// model number already exists
		if ($model_stmt->rowCount() > 0) {
			$submit_status['success'] = false;
			$submit_status['errors'] = array("Error: Duplicate model number $model.");
			$conn->rollBack();
		}

		// no errors, so model number was unique
		if (empty($submit_status)) {
			// add the product to the product table
			$sql = "INSERT INTO product (model, type, maker) VALUES(:model_num, 'pc', :maker)";
			$product_insert = $conn->prepare($sql);
			$product_insert->bindParam(':model_num', $pc_details['model']);
			$product_insert->bindParam(':maker', $pc_details['maker']);
			$product_insert->execute();

			// add the product to the pc table
			$sql = "INSERT INTO pc (model, speed, ram, hd, price) VALUES(?, ?, ?, ?, ?)";
			$pc_insert = $conn->prepare($sql);
			$pc_insert->bindParam(1, $pc_details['model']);
			$pc_insert->bindParam(2, $pc_details['speed']);
			$pc_insert->bindParam(3, $pc_details['ram']);
			$pc_insert->bindParam(4, $pc_details['hd']);
			$pc_insert->bindParam(5, $pc_details['price']);
			$pc_insert->execute();

			$conn->commit();

			// inserts were successful
			$submit_status['success'] = true;
			$submit_status['new_model'] = $pc_details['model'];
		}
	} catch (PDOException $e) {
		$submit_status['success'] = false;
		$submit_status['errors'] = array($e->getMessage());
	}

	return $submit_status;
}

/* Main Code Entry Point */

// hash where we will store the status of the user's submission
$submit_status = array();
$input = null;

// get the form input
if (!empty($_POST)) {
	$input = get_input_array();

	$maker = $input['maker'];
	$model = $input['model'];
	$speed = $input['speed'];
	$ram   = $input['ram'];
	$hd    = $input['hd'];
	$price = $input['price'];	

	$validation_errors = validate_input($input);
	if (!empty($validation_errors)) {
		$submit_status['success'] = false;
		$submit_status['errors'] = $validation_errors;
	} else {
		$submit_status = insert_new_pc($input);

		// clear form values on successful submit
		if (empty($submit_status['errors'])) {
			$maker = $model = $speed = $ram = $hd = $price = '';
		}
	}
}

// get the list of manufacturers for form's pull-down list
try {
	$conn = connect_to_db();

	$maker_stmt = $conn->query('SELECT DISTINCT maker FROM product ORDER BY maker');
	$maker_list = $maker_stmt->fetchAll();
	$maker_stmt->closeCursor();
} catch (PDOException $e) {
	echo "Database exception: " . $e->getMessage() . "\n";
	die;
}

disconnect_from_db();

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add a New PC</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/custom.css">
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
	<div class="row">
		<div class="offset2 span8">
<?php
			// report successful form submission
			if ($submit_status['success']) {
				printf('<div class="alert alert-success">Added product %s.</div>', 
						$submit_status['new_model']);
			}
?>

			<h2>Add a New Product</h2>

			<form action="e.php" method="post" class="form-horizontal">
<?php
				// display errors that occurred on submission
				if (isset($submit_status['success']) && !$submit_status['success']) {
					print '<div class="alert alert-error">';
					print '<h3>There were some problems with your input:</h3>';
					foreach ($submit_status['errors'] as $error) {
						print '<p>' . $error . '</p>';
					}
					print '</div>';
				}
?>
				<div class="control-group">
					<label for="makerList" class="control-label">Manufacturer</label>
					<div class="controls">
						<select id="makerList" name="maker">
<?php
						// construct the drop-down list of makers from databse results
						foreach ($maker_list as $maker_row) {
							$selected = ($maker_row['maker'] == $maker) ? 'selected="selected"' : '';
							printf('<option value="%s" %s>%s</option>', $maker_row['maker'], 
								$selected, $maker_row['maker']);
						}
?>
						</select>
					</div>
				</div>

				<div class="control-group">
					<label for="newMakerField" class="control-label">New Manufacturer</label>
					<div class="controls">
						<input type="text" id="newMakerField" name="new_maker" value=""/>
					</div>
				</div>

				<div class="control-group">
					<label for="modelField" class="control-label">Model Number</label>
					<div class="controls">
						<input type="text" id="modelField" name="model" value="<?php echo $model; ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label for="speedField" class="control-label">CPU Speed</label>
					<div class="controls">
						<input type="text" id="speedField" name="speed" value="<?php echo $speed; ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label for="ramField" class="control-label">RAM in MB</label>
					<div class="controls">
						<input type="text" id="ramField" name="ram" value="<?php echo $ram; ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label for="hdField" class="control-label">HD Size in GB</label>
					<div class="controls">
						<input type="text" id="hdField" name="hd" value="<?php echo $hd; ?>"/>
					</div>
				</div>

				<div class="control-group">
					<label for="priceField" class="control-label">Price</label>
					<div class="controls">
						<input type="text" id="priceField" name="price" value="<?php echo $price; ?>"/>
					</div>
				</div>

				<div class="controls">
					<input type="submit" value="Add Product" class="btn btn-primary"/>
				</div>
			</form>
			</div>
	</div>
</body>
</html>
