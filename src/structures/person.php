<?php
require_once ROOT . 'utils/function.php';


class Person
{
    private $id;
    private $fname;
    private $lname;
    private $email;
    private $pass;
    private $admin;
    private $gender;
    private $vegetarian;
    private $budget;
    private $location;
    private $created_at;
    private $failed_attempts;

    function __construct($email)
    {
        $this->email = $email;
    }

    function authenticate($pass, $dbCon)
    {
        $loginUser = QueryBuilder::create($dbCon)
            ->select()
            ->from('users')
            ->where('email', '=', $this->email)
            ->first()
            ->execute();

        $loginFlag = "email";
        $attempt = null;

        // If the user is locked
        if ($loginUser && $loginUser["failed_attempts"] <= 0) {  // need to account lock
            $loginFlag = "lock";
        } elseif ($loginUser) {
            // Get the number of failed attempts
            $attempt = $loginUser["failed_attempts"];

            // Check if the password is correct
            if (password_verify($pass, $loginUser['pass'])) {  //$row['pass'] means hash password

                // If the password is correct, reset the failed attempts
                $loginFlag = true;
                $attempt = 5;

                // Set the user's data
                $this->fname = $loginUser['fname'];
                $this->lname = $loginUser['lname'];
                $this->gender = $loginUser['gender'];
                $this->vegetarian = $loginUser['vegetarian'];
                $this->budget = $loginUser['budget'];
                $this->admin = $loginUser['admin'];
                $this->location = $loginUser['location'];
                $this->id = $loginUser['id'];
                $this->created_at = $loginUser['created_at'];
                $this->failed_attempts = $loginUser['failed_attempts'];

                // Start the session
                $sectionStarted = session_start();

                // If the session could not be started
                if (!$sectionStarted) {
                    throw new Exception("Session could not be started.", 500);
                }

                // Set the user's data in the session
                $_SESSION["user"] = $this->display_info();
                // Set the timestamp
                $_SESSION["timestamp"] = time();

                // Audit_generator("login", "success", "User login via password.", $this->email);
            } else {
                // If the password is incorrect, decrement the number of attempts
                $attempt -= 1;
                $loginFlag = "pass"; // because of password
            }

            // Update the number of failed attempts
            QueryBuilder::create($dbCon)
                ->update()
                ->table('users')
                ->set(['failed_attempts' => $attempt])
                ->where('email', '=', $this->email)
                ->execute();
        }

        //$insertCmd = "INSERT INTO user "
        // UPDATE [table_name] SET [col_name]= new_nalue WHERE condition 
        if ($loginFlag !== true) {
            switch ($loginFlag) {
                case "email":
                    Audit_generator("login", "failed", "Invalid email address.", $this->email);
                    send_error_response("Username/Password Wrong.", 401);

                case "pass":
                    Audit_generator("login", "failed", "Invalid password. Attempts(" . $attempt . ")", $this->email);
                    send_error_response("Username/Password Wrong.", 401);

                case "lock":
                    Audit_generator("login", "failed", "account locked. Attempts(" . $attempt . ")", $this->email);
                    send_error_response("Account is locked.", 401);
                default:
                    break;
            }
        }

        Audit_generator("login", "success", "Login success.", $this->email);
        return $this->display_info();
    }
    function display_info()
    {
        return [
            'id' => $this->id,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
            'gender' => $this->gender,
            'vegetarian' => $this->vegetarian,
            'admin' => $this->admin,
            'budget' => $this->budget,
            'location' => $this->location,
            'created_at' => $this->created_at,
            'failed_attempts' => $this->failed_attempts,
        ];
    }
}
