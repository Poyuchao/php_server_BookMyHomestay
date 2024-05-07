<?php
require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'structures/Route.php';
require_once ROOT . 'structures/person.php';
require_once ROOT . 'utils/function.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'utils/config.php';


$POST_LOGIN = Route::path('/login')
    ->setMethod('POST')
    ->setHandler(function ($_, Database $database) {


        $email = $_POST['email'];
        // $password = password_hash($_POST["pass"], PASSWORD_BCRYPT, ["cost" => 10]);
        $pass = $_POST['pass'];



        // print_r("here is email: ".$email);
        // print_r("here is pass: ".$pass);

        checkKeys($_POST, ["email", "pass"]);

        // print_r($loginUser);
        // try {
        //     $sid = $_POST['sid'];
        //     Session_Hanlder($sid);
        //     echo json_encode(["message" => "Session is valid and active."]);
        // } catch (Exception $e) {
        //     http_response_code($e->getCode());
        //     echo json_encode(["error" => $e->getMessage()]);
        // }


        // if (isset($_POST["sid"])) {
        //     Session_Hanlder($_POST["sid"]);
        // }

        try {
            $person = new Person($email);
            $jsonUser = $person->authenticate($pass, $database->connection);

            // print_r($jsonUser);
            send_response([
                'user' => $jsonUser,
                'session' => session_id()
            ], 201);
        } catch (Exception $e) {

            send_error_response('Login failed', 400);
        }
    })

    ->build();
