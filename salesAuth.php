<?php

class sAuth {

  /*user's firstname*/
  private $name;

  /*user's secondname*/
  private $surname;

  /*user's email*/
  private $email;

  /* user's password */
  private $pswd;

  /* database */
  private $db;

  /* generic error */
  const GENERIC_ERROR = "Error: database's server may not work properly or may not exists";

  const NOT_REGISTERED_ERROR = "Error: user is not registered";
  
  const EXCEPTION = "Exception: ";

  function _construct ($email, $pswd, $name = null, $surname = null) {
  
    $data = file_get_contents('resources/config.json');
    $data = json_decode($data, true);
    $this->initDatabase($data['user'], $data['address'], $data['port'], $data['password']);

    $this->email = strtolower($email);
    $this->pswd = $pswd;
    $this->name = strtolower($name);
    $this->surname = strtolower($surname);

  }

  private function initDatabase ($user, $address, $port, $password) {

    $db = new mysqli($address, $user, $password, null, $port);
    
    if(!$db->connect_error){
      if ($db->query('CREATE DATABASE demix') == false) {
        $db->select_db('demix');
        $sql = 'CREATE TABLE users (
          id INT NOT NULL AUTO_INCREMENT,
          firstname VARCHAR(30) NOT NULL,
          lastname VARCHAR(30) NOT NULL,
          email VARCHAR(50) NOT NULL,
          password VARCHAR(30) NOT NULL,
          cash INT NOT NULL default 0,
          PRIMARY KEY (id)
        )';
        $db->query($sql);
      }
      $db->select_db('demix');
    }else {
      return self::GENERIC_ERROR;
    }
    $this->db = $db;
  }
  
  
  public function isRegistered () {
    if($this->db instanceof mysqli && $this->db->ping()) {
      $data = $this->getData()['email'];
      if($data != null){
        return true;
      } else {
        return false;
      }
    } else {
      return self::GENERIC_ERROR;
    }
  }
  
  public function register () {
    if($this->db instanceof mysqli && $this->db->ping()) {
      try {
        $query = $this->db->prepare('INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?);');
        $query->bind_param('ssss', $this->name, $this->surname, $this->email, $this->pswd);
        $query->execute();
        return $this;
      } cath(Exception $e){
        return $e;
      }
    } else {
      return self::GENERIC_ERROR;
    }
  }
  
  public function authenticate () {
    if($this->db instanceof mysqli && $this->db->ping()) {
      if($this->isRegistered()) {
        if($this->getData['password'] == $this->pswd) {
           return true;
        }else {
          return false;
        }
      } else {
        return self::NOT_REGISTERED_ERROR;
      }
    } else {
      return self::GENERIC_ERROR;
    }
  }
  
  public function getData () {
    if($this->db instanceof mysqli && $this->db->ping()) {
      try {
        $this->db->multi_query('SELECT * FROM users WHERE email="'.$this->email.'";');
        $res = $this->db->store_result();
        $res = mysqli_fetch_assoc($res);
        return $res;
      } catch( Exception $e ) {
        return self::EXCEPTION . $e;
      }
    } else {
      return self::GENERIC_ERROR;
    }
  }

  function __destruct () {
    $this->db->close();
    unset($this->email, $this->pswd, $this->name, $this->surname, $this->db);
  }
  
}
?>
