<?php
require_once ROOT . 'utils/function.php';


class Person
{
    private $id;
    private $fname;
    private $lname;
    private $email;
    private $pass;
    private $gender;
    private $vegetarian;
    private $budget;
    private $type;
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
        if ($loginUser) { //クエリの結果が1行以上ある


            $attempt = $loginUser["failed_attempts"];

            if ($loginUser["failed_attempts"] == 0) {  // need to account lock

            }
            if (password_verify($pass, $loginUser['pass'])) {  //$row['pass'] means hash password

                $loginFlag = true;
                $attempt = 5;

                $this->fname = $loginUser['fname'];
                $this->lname = $loginUser['lname'];
                $this->gender = $loginUser['gender'];
                $this->vegetarian = $loginUser['vegetarian'];
                $this->budget = $loginUser['budget'];

                $this->location = $loginUser['location'];
                $this->type = $loginUser['type'];
                $this->id = $loginUser['id'];
                $this->created_at = $loginUser['created_at'];
                $this->failed_attempts = $loginUser['failed_attempts'];
                session_start();
                $_SESSION["login_user"] = $this;
                $_SESSION["time_out"] = time() + TIME_OUT;


                // Audit_generator("login", "success", "User login via password.", $this->email);
            } else { //ログインがうまくいかなかったら 
                $attempt -= 1;
                $loginFlag = "pass"; // because of password
            }

            QueryBuilder::create($dbCon)
                ->update()
                ->table('users')
                ->set(['failed_attempts' => $attempt])
                ->where('email', '=', $this->email)
                ->execute();
        }



        // UPDATE [table_name] SET [col_name]= new_nalue WHERE condition 
        if ($loginFlag !== true) {
            // print_r("login flag " . $loginFlag . "\n");
            switch ($loginFlag) {
                case "email":

                    //Audit_generator("login", "failed", "Invalid email address.", $this->email);
                    throw new Exception("Username/Password Wrong.", 401);

                case "pass":
                    //Audit_generator("login", "failed", "Invalid password. Attempts(" . $attempt . ")", $this->email);
                    throw new Exception("Username/Password Wrong.", 401);
            }
        }

        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
            'gender' => $this->gender,
            'type' => $this->type,
            'vegetarian' => $this->vegetarian,
            'budget' => $this->budget,
            'location' => $this->location,
            'created_at' => $this->created_at,
            'failed_attempts' => $this->failed_attempts,
        ];
    }
}
