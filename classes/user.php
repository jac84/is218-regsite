<?php

include('passwd.php');

class User extends Password{

    private $_db;

    function __construct($db){
    	parent::__construct(); //defines password class
    	$this->_db = $db;
    }

	public function get_user_hash($username){ //reset to private after testing
		try {
			$stmt = $this->_db->prepare('SELECT password, username, userId FROM RegSiteUsers WHERE username = :username AND active="1" ');
			$stmt->execute(array('username' => $username));
			return $stmt->fetch();
		} catch(PDOException $e) {
		    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
		}
	}

}
?>
