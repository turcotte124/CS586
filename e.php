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
 * Modified: 2012-11-20
 *
 */

// import the database connection information
require_once 'db-config.php';

// Attempt to connect to the database
try {
	// create DB connection object
	$conn = new PDO($db_connection_string, DB_USER, DB_PASS);

	// throw exceptions when errors occur
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// fetch rows as associative arrays (dictionaries)
	$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Database exception: " . $e->getMessage() . "\n";
  die;
}

// Get the form input
$maker = isset($_POST['maker']) ? $_POST['maker'] : '';
$model = isset($_POST['model']) ? $_POST['model'] : '';
$speed = isset($_POST['speed']) ? $_POST['speed'] : '';
$ram   = isset($_POST['ram']) ? $_POST['ram'] : '';
$hd    = isset($_POST['hd']) ? $_POST['hd'] : '';
$price = isset($_POST['price']) ? $_POST['price'] : '';

// use the new_maker field instead of the maker field if it's set
$new_maker = isset($_POST['new_maker']) ? $_POST['new_maker'] : '';
if (!empty($new_maker)) {
	$maker = $new_maker;
}

$success = false;
$duplicate = false;
$transaction_failure = null;

// get the list of manufacturers for form's pull-down list
try {
	$maker_stmt = $conn->query('SELECT DISTINCT maker FROM product ORDER BY maker');
	$maker_list = $maker_stmt->fetchAll();
} catch (PDOException $e) {
	echo "Database exception: " . $e->getMessage() . "\n";
	die;
}

// form submitted, so try to insert the new product
if (!empty($model)) {
	try {
		$conn->beginTransaction();

		// try to find a product with the given model number
		$model_stmt = $conn->prepare("SELECT * FROM product WHERE model = :model_num");
		$model_stmt->bindParam(':model_num', $model);
		$model_stmt->execute();

		// model number already exists
		if ($model_stmt->rowCount() > 0) {
			$duplicate = true;
			$conn->rollBack();
		}

		if (!$duplicate) {
			// add the product to the product table
			$sql = "INSERT INTO product (model, type, maker) VALUES(:model_num, 'pc', :maker)";
			$product_insert = $conn->prepare($sql);
			$product_insert->bindParam(':model_num', $model);
			$product_insert->bindParam(':maker', $maker);
			$product_insert->execute();

			// add the product to the pc table
			$sql = "INSERT INTO pc (model, speed, ram, hd, price) VALUES(?, ?, ?, ?, ?)";
			$pc_insert = $conn->prepare($sql);
			$pc_insert->bindParam(1, $model);
			$pc_insert->bindParam(2, $speed);
			$pc_insert->bindParam(3, $ram);
			$pc_insert->bindParam(4, $hd);
			$pc_insert->bindParam(5, $price);
			$pc_insert->execute();

			$conn->commit();

			// inserts were successful
			$success = true;
			$added_model = $model;

			// clear form input
			$maker = $model = $speed = $ram = $hd = $price = '';		
		}
	} catch (PDOException $e) {
		$transaction_failure = 'Exception in transaction: ' . $e->getMessage();
	}
}

// close db connection
$conn = null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add a New PC</title>
  <link rel="stylesheet" href="css/bootstrap.css">
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
<body>
	<div class="row">
		<div class="offset2 span8">
<?php
			// Report result of form submission

			// transaction was successful
			if ($success) {
				printf('<div class="text-success">Added product %s.</div>', $added_model);
			}

			// duplicate model number found
			if ($duplicate) {
				printf('<div class="text-warning">Please change the model number. %s is a duplicate.</div>', $model);
			}

			// exception occurred in transaction portion
			if (!empty($transaction_failure)) {
				printf('<div class="text-failure">%s</div>', $transaction_failure);
			}
?>

			<h2>Add a New Product</h2>

			<form action="e.php" method="post" class="form-horizontal">
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
						<input type="text" id="newMakerField" name="new_maker" value="<?php echo $new_maker; ?>"/>
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
						<input type="text" name="ramField" value="<?php echo $ram; ?>"/>
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
