<?php
public function login($uname, $passwd){
  if($this->password_verify($passwd) == 1){
    $_SESSION['loggedin'] = true
    return true;
  }
}

//form submit
if(isset($_POST['submit'])){
  $uname = $_POST['uname'];
  $passwd = $_POST['password'];

  if($user->login($uname, $passwd)){
    echo "Login Successful!";
    exit;

  } else {
    $error[] = 'Your username or password is wrong.';
  }
}

//check database
try {
  $conn = new PDO('mysql:host = sql1.njit.edu'; dbname='jac84', $uname, $passwd);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $loginquery = sprintf("SELECT * FROM Users" WHERE Username = $uname AND Password = $passwd", mysql_real_escape_string($uname), mysql_real_escape_string($passwd));
  $result = mysql_query($loginquery);
  $result = $sth->fetch(PDO::FETCH_ASSOC);
  print_r($result)

} catch (PDOException $ex) {
  echo "Error: " . $e->getMessage();
}







?>
