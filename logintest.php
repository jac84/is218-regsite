<?php

require_once('includes/config.php');

//check if already logged in move to home page
if( $user->is_logged_in() ){ header('Location: index.php'); }

//process login form if submitted
if(isset($_POST['submit'])){
	$uname = $_POST['username'];
	$passwd = $_POST['password'];

	if($user->login($uname,$passwd)){
		$_SESSION['username'] = $uname;
		header('Location: userprofile.php');
		exit;

	} else {
		$error[] = 'Username or password is wrong';
	}
}

$title = 'Login';
//include header template
require('layout/header.php');

			//check for any errors
				if(isset($error)){
					foreach($error as $error){
						echo '<p class="bg-danger">'.$error.'</p>';
					}
				}
				if(isset($_GET['action'])){
					//check the action
					switch ($_GET['action']) {
						case 'active':
							echo "<h2 class='bg-success'>Account is active. Please log in.</h2>";
							break;
						case 'reset':
							echo "<h2 class='bg-success'>Look for reset link.</h2>";
							break;
						case 'resetAccount':
							echo "<h2 class='bg-success'>Password change successful.</h2>";
							break;
					}
				}

				?>

				<div class="form-group">
					<input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo $_POST['username']; } ?>" tabindex="1">
				</div>

				<div class="form-group">
					<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
				</div>

				<div class="row">
					<div class="col-xs-9 col-sm-9 col-md-9">
						 <a href='reset.php'>Forgot your Password?</a>
					</div>
				</div>

				<hr>
				<div class="row">
					<div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Login" class="btn btn-primary btn-block btn-lg" tabindex="5"></div>
				</div>
			</form>
		</div>
	</div>



</div>


<?php
//include header template
require('layout/footer.php');
?>
