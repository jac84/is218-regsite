<?php
function login($uname, $passwd){
  if($this->password_verify($passwd) == 1){
    $_SESSION['loggedin'] = true;
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






?>
