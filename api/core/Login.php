<?php

class Login {

    private $session;
    private $conn;
    private $username;
    private $password;
    private $hash;

    /*
     * The constructor
     * 
     * @param $username string the username
     * @param $password string the password
     */

    public function __construct(string $username, string $password) {

        if(Config::DB_HOST != null && Config::DB_DATABASE != null 
            && Config::DB_USER != null && Config::DB_PASSWORD != null) {
            if(Config::DB_USER_TABLE != null && Config::DB_USERNAME_ROW != null 
                && Config::DB_PASSWORD_ROW != null) {
                $this->conn = Database::getInstance()->getConn();
                $this->session = Session::getInstance();
                $this->username = Util::sanitizeString($username, $this->conn);
                $this->password = $password;
            } else {
                View::json(DefaultHandler::loginMissingConfiguration());
            }
        } else {
            View::json(DefaultHandler::databaseMissingCredentials());
        }

    }

    public function isLoginValid() : bool {
        $isValid = false;

        if($this->userExists()) {
            if(password_verify($this->password, $this->hash)) {
                $this->session->setSessionVar("isLogged", true);
                $isValid = true;
            } 
        }

        return $isValid;
    }

    private function userExists() : bool {
        $exists = false;
        
        $query = sprintf("SELECT * FROM %s WHERE %s = '%s'", Config::DB_USER_TABLE, Config::DB_USERNAME_ROW, $this->username);
        $result = $this->conn->query($query);

        if($result->num_rows == 1) {
            $result = $result->fetch_array(MYSQLI_ASSOC);
            $this->hash = $result[Config::DB_PASSWORD_ROW];
            $exists = true;
        }

        return $exists;
    }

}

?>