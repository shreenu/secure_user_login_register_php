<?php
class user{   
    protected static $connection;
    
    Const SECURE = FALSE;//For Development Only


    public function connect(){
        if(!isset(self::$connection)) {
            $config = parse_ini_file(__DIR__.'/config.ini');
            self::$connection = new mysqli($config['HOST'],$config['USER'],$config['PASSWORD'],$config['DATABASE']);
        }
        
        if(self::$connection == false){
            echo "error in connection";
            return false;
        }
        return self::$connection;
    }
    
    public function sec_session_start() {
        // Set a custom session name
        $session_name = 'sec_app_session_name';
        $secure = self::SECURE;
        
        // This stops JavaScript being able to access the session id.
        $httponly = true;
        
        // Forces sessions to only use cookies.
        if (ini_set('session.use_only_cookies', 1) === FALSE) {
            header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
            exit();
        }
        
        // Gets current cookies params.
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($cookieParams["lifetime"],
            $cookieParams["path"], 
            $cookieParams["domain"], 
            $secure,
            $httponly);
        
        // Sets the session name to the one set above.
        // Start Session.
        // regenerated the session, delete the old one. 
        session_name($session_name);
        session_start();
        session_regenerate_id(true);
    }
    
    public function login($email, $password) {
        $mysqli = $this->connect();
        // Using prepared statements means that SQL injection is not possible. 
        if ($stmt = $mysqli->prepare("SELECT id, username, password, salt 
                                    FROM members
                                    WHERE email = ?
                                    LIMIT 1")){
            $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
            $stmt->execute();    // Execute the prepared query.
            $stmt->store_result();
            
            // get variables from result.
            $stmt->bind_result($user_id, $username, $db_password, $salt);
            $stmt->fetch();

            // hash the password with the unique salt.
            $password = hash('sha512', $password . $salt);
            
            if ($stmt->num_rows == 1) {
                // If the user exists we check if the account is locked
                // from too many login attempts 

                if ($this->checkbrute($user_id) == true) {
                    // Account is locked 
                    // Send an email to user saying their account is locked
                    return false;
                } else {
                    // Check if the password in the database matches
                    // the password the user submitted.
                    if ($db_password == $password){
                        // Password is correct!
                        // Get the user-agent string of the user.
                        $user_browser = $_SERVER['HTTP_USER_AGENT'];
                        // XSS protection as we might print this value
                        $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                        $_SESSION['user_id'] = $user_id;
                        // XSS protection as we might print this value
                        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", 
                                                                    "", 
                                                                    $username);
                        $_SESSION['username'] = $username;
                        $_SESSION['login_string'] = hash('sha512', 
                                  $password . $user_browser);
                        // Login successful.
                        return true;
                    } else {
                        // Password is not correct
                        // We record this attempt in the database
                        $now = time();
                        $mysqli->query("INSERT INTO login_attempts(user_id, time)
                                        VALUES ('$user_id', '$now')");
                        return false;
                    }
                }
            } else {
                // No user exists.
                return false;
            }
        }
    }
    
    
    public function checkbrute($user_id) {
        $mysqli = $this->connect();
        
        // Get timestamp of current time 
        $now = time();

        // All login attempts are counted from the past 2 hours. 
        $valid_attempts = $now - (2 * 60 * 60);

        if ($stmt = $mysqli->prepare("SELECT time 
                                 FROM login_attempts 
                                 WHERE user_id = ? 
                                AND time > '$valid_attempts'")) {
            $stmt->bind_param('i', $user_id);

            // Execute the prepared query. 
            $stmt->execute();
            $stmt->store_result();

            // If there have been more than 5 failed logins 
            if ($stmt->num_rows > 5) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    public function login_check() {
        $mysqli = $this->connect();
        // Check if all session variables are set 
        if (isset($_SESSION['user_id'],
            $_SESSION['username'], 
            $_SESSION['login_string'])) {

            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            $username = $_SESSION['username'];

            // Get the user-agent string of the user.
            $user_browser = $_SERVER['HTTP_USER_AGENT'];

            if ($stmt = $mysqli->prepare("SELECT password 
                                          FROM members 
                                          WHERE id = ? LIMIT 1")) {
                // Bind "$user_id" to parameter. 
                $stmt->bind_param('i', $user_id);
                $stmt->execute();   // Execute the prepared query.
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    // If the user exists get variables from result.
                    $stmt->bind_result($password);
                    $stmt->fetch();
                    $login_check = hash('sha512', $password . $user_browser);

                    if ($login_check == $login_string) {
                        // Logged In!!!! 
                        return true;
                    } else {
                        // Not logged in 
                        return false;
                    }
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Not logged in 
            return false;
        }
    }
    
    
    public function esc_url($url) {
 
        if ('' == $url) {
            return $url;
        }

        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

        $strip = array('%0d', '%0a', '%0D', '%0A');
        $url = (string) $url;

        $count = 1;
        while ($count) {
            $url = str_replace($strip, '', $url, $count);
        }

        $url = str_replace(';//', '://', $url);

        $url = htmlentities($url);

        $url = str_replace('&amp;', '&#038;', $url);
        $url = str_replace("'", '&#039;', $url);

        if ($url[0] !== '/') {
            // We're only interested in relative links from $_SERVER['PHP_SELF']
            return '';
        } else {
            return $url;
        }
    }
    
    public function logout(){
        $_SESSION = array();
 
        // get session parameters 
        $params = session_get_cookie_params();

        // Delete the actual cookie. 
        setcookie(session_name(),
                '', time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]);
        
        // Destroy session 
        session_destroy();
        return true;
    }
    
    public function register($username, $email, $p){
        $mysqli = $this->connect();
        $return_array = ["error_status"=>FALSE,"error_email"=>null,"error_password"=>null,"error_msg"=>null,"error_username"=>null];
        if (isset($username, $email, $p)) {
            // Sanitize and validate the data passed in
            $username = $this->filter($username);
            $email = $this->filter($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Not a valid email
                $return_array["error_password"] = "The email address you entered is not valid";
                $return_array["error_status"] =true;
                return $return_array;
            }

            $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
            if (strlen($password) != 128) {
                // The hashed pwd should be 128 characters long.
                // If it's not, something really odd has happened
                $return_array["error_password"] = 'Invalid password configuration';
                $return_array["error_status"] =true;
                return $return_array;
            }

            // Username validity and password validity have been checked client side.
            // This should should be adequate as nobody gains any advantage from
            // breaking these rules.

            $prep_stmt = "SELECT id FROM members WHERE email = ? LIMIT 1";
            $stmt = $mysqli->prepare($prep_stmt);

            // check existing email  
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    // A user with this email address already exists
                    $return_array["error_email"] = 'A user with this email address already exists.';
                    $return_array["error_status"] =true;
                    $stmt->close();
                    return $return_array;
                }
                $stmt->close();
            } else {
                $return_array["error_msg"] = 'Database error email selection';
                $return_array["error_status"]=true;
                $stmt->close();
                return $return_array;
            }

            // check existing username
            $prep_stmt = "SELECT id FROM members WHERE username = ? LIMIT 1";
            $stmt = $mysqli->prepare($prep_stmt);

            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    // A user with this username already exists
                    $return_array["error_username"] = 'A user with this username already exists';
                    $return_array["error_status"] = true;
                    $stmt->close();
                    return $return_array;
                }
                $stmt->close();
            } else {
                $return_array["error_msg"] = 'Database error username selection';
                $return_array["error_status"] = true;
                $stmt->close();
                return $return_array;
            }


            if($return_array["error_status"] === false) {
                // Create a random salt
                $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

                // Create salted password 
                $password = hash('sha512', $password . $random_salt);

                // Insert the new user into the database 
                if ($insert_stmt = $mysqli->prepare("INSERT INTO members (username, email, password, salt) VALUES (?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('ssss', $username, $email, $password, $random_salt);
                    // Execute the prepared query.
                    if (! $insert_stmt->execute()) {
                        $return_array["error_msg"] = 'Database error user insert';
                        $return_array["error_status"] = true;
                        return $return_array;
                    }
                }
            }
        }
        return $return_array;
    }
    
    public function filter($data) {
        $mysqli = $this->connect();
        if (is_array($data)) {
            foreach ($data as $key => $element) {
                $data[$key] = filter($element);
            }
        } else {
            $data = trim(htmlentities(strip_tags($data)));
            if(get_magic_quotes_gpc()) $data = stripslashes($data);
            $data = $mysqli->real_escape_string($data);
        }
        return $data;
    }
}