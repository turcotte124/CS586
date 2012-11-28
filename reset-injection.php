<?php
/* reset-injection.php: Undoes changes made when demonstrating injection.php,
 * setting the value of PC model 1001 back to $2114.
 *
 * Author:   Heath Harrelson <harrel2@pdx.edu>
 * Modified: 2012-11-27
 *
 */
require_once 'db-config.php';

$success = false;
$error = null;

try {
	$conn = new PDO($pdo_connection_string, DB_USER, DB_PASS);

	$sql = 'UPDATE pc SET price = 2114 WHERE model = 1001';
	if ($conn->query($sql)) {
		$success = true;
	} else {
		$error_info = $conn->errorInfo();
		$error = $error_info[2]; // get error message
	}	
} catch (PDOException $e) {
	$error = $e->getMessage();
}

$conn = null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset SQL Injection Demo</title>
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
<?php
		if ($success) {
			echo '<p>The demo was reset successfully.</p>';
		} else {
			echo '<div class="alert alert-error>';
			echo '    <p>Could not reset. Error was: ' . $error . '</p>';
			echo '</div>';
		}
?>

		</div>
	</div>

</body>
</html>
