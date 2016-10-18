<!DOCTYPE html>
<html>
<head>
	<title> Sign up today! </title>
</head>

<body>
<form action = "form.php" method = "post">
	<div>
		<center>
		<label for = "name"> Name: </label> <br>
		<input type = "text" id = "name" name = "user_name" /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = "mail" > E-mail: </label> <br>
		<input type = "email" id = "mail" name = "user_mail" /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = “username”> Username: </label> <br>
		<input type = “text” id = “username” name = “user_username” /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = “age”> Age: </label> <br>
		<input type = “text” id = “age” name = “user_age” /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = “gender”> Gender: </label> <br>
		<input type = “radio” name = "gender" value = "male" Male /> <br>
		<input type = "radio" name = "gender" value = "female" Female /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = “password”> Password: </label> <br>
		<input type = “text” id = “password” name = “user_password” /> <br>
		</center>
	</div>
	<div>
		<center>
		<label for = “passwordconf”> Confirm Passowrd: </label> <br>
		<input type = “text” id = “passwordconf” name = “user_passwordconf” /> <br>
		</center>
	</div>
	<div>
		<center>
		<div class = "button"> <br>
		<button type = "submit">Thank you for registering!</button>
		</center>
	</div>
</body>
</html>
</form>


