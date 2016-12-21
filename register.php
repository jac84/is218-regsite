<?php
class register
{
  public function register($uname,$umail,$upass)
  {
    try
    {
      $stmt = $this->db->prepare("INSERT INTO RegSiteUsers(user_name,user_email,user_pass)
                                                       VALUES(:uname, :umail, :upass)");
      $stmt->bindparam(":uname", $uname);
      $stmt->bindparam(":umail", $umail);
      $stmt->bindparam(":upass", $upass);
      $stmt->execute();

      return True;
    }
    catch(PDOException $e)
    {
      echo $e->getMessage();
    }
  }

  public function checkValidation($uname,$umail,$upass)
  {
    if (isset($POST['submit']))
    {
      $uname = trim($_POST['uname']);
      $umail = trim($_POST['email']);
      $upass = trim($_POST['passwd']);

      // switch/case statement
      if(isEmpty($uname))
      {
        $error[] = "provide username !";
      }
      else if(isEmpty($umail) && !filter_var($umail, FILTER_VALIDATE_EMAIL))
      {
        $error[] = "provide valid email address";
      }
      /*else if(!filter_var($umail, FILTER_VALIDATE_EMAIL))
      {
        $error[] = 'Please enter a valid email address !';
      }*/
      else if(isEmpty($upass))
      {
        $error[] = "provide password !";
      }
      else
      {
        try
        {

          /*$stmt = $DB_con->prepare("SELECT user_name,user_email FROM RegSiteUsers WHERE user_name=:uname OR user_email=:umail");
          $stmt->execute(array(':uname'=>$uname, ':umail'=>$umail));
          $row=$stmt->fetch(PDO::FETCH_ASSOC);*/



          if($row['user_name']==$uname)
          {
            $error[] = "sorry username already taken !";
          }
          else if($row['user_email']==$umail)
          {
            $error[] = "sorry email id already taken !";
          }
          else
          {
            if(register($uname,$umail,$upass))
            {
              echo 'Registration Successful';

            }
          }
        }
        catch(PDOException $e)
        {
          echo $e->getMessage();
        }
    }
  }
}
}
?>
