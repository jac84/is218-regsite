<!DOCTYPE html>

<form action = "form.php" method = "post">
	<div>
		<label for = "name"> Name: </label>
		<input type = "text" id = "name" name = "user_name" />
	</div>
	<div>
		<label for = "mail" > E-mail: </label>
		<input type = "email" id = "mail" name = "user_mail" />
	</div>
	<div>
		<label for = “username”> Username: </label>
		<input type = “text” id = “username” name = “user_username” />

	</div>
	<div>
		<label for = “age”> Age: </label>
		<input type = “text” id = “age” name = “user_age” />
	</div>
	<div>
		<label for = “gender”> Gender: </label>
		<input type = “radio” name = "gender" value = "male" Male />
		<input type = "radio" name = "gender" value = "female" Female />
	</div>
	<div>
		<label for = “password”> Password: </label>
		<input type = “text” id = “password” name = “user_password” />
	</div>
	<div>
		<label for = “passwordconf”> Confirm Passowrd: </label>
		<input type = “text” id = “passwordconf” name = “user_passwordconf” />
	</div>
	<div>
		<div class = "button">
		<button type = "submit">Thank you for registering!</button>
	</div>
</form>


