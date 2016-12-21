<?php

require('includes/config.php');
require('layout/header.php');

if(!$user->is_logged_in()){ header('Location: login.php'); } //if not logged in redirect to login page

$title = 'Members Page';

?>

<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

				<h2>Member only page - Welcome <?php echo $_SESSION['username']; ?></h2>
				<p><a href='logout.php'>Logout</a></p>
				<hr>

		</div>
	</div>


</div>

<?php
require('layout/footer.php');
?>
