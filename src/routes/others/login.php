<?php
require_once 'utils/send-response.php';
require_once 'structures/Route.php';
require_once 'structures/Person.php';

// original data
// $POST_LOGIN = Route::path('/login')
//     ->setMethod('POST')
//     ->setHandler(function ($params, Database $database) {
//         var_dump(QueryBuilder::create($database->connection)
//             ->select()
//             ->from('users')
//             ->where('email', '=', $_POST['email'])
//             ->first()
//             ->execute());
//     })
//     ->build();


// function login($UserEmail)
// {
//     $sql = "SELECT * FROM users WHERE email = '$UserEmail';";
// }


// thinking data
// $POST_LOGIN = Route::path('/login')
//     ->setMethod('POST')
//     ->setHandler(function ($params, Database $database) {
//         var_dump(QueryBuilder::create($database->connection)
//             ->select()
//             ->from('users')
//             ->where('email', '=', $_POST['email'])
//             ->first()
//             ->execute());
//             $ new Person()
//             if(){

//             }else{

//             }

//     })
//     ->build();


// function login($UserEmail)
// {
//     $sql = "SELECT * FROM users WHERE email = '$UserEmail';";
// }


// $POST_LOGIN = Route::path('/login')
//     ->setMethod('POST')
//     ->setHandler(function ($params, Database $database) {
//         $email = $_POST['email'];
//         // $password = password_hash($_POST["pass"], PASSWORD_BCRYPT, ["cost" => 10]);
//         $pass = $_POST['pass'];
//         // echo $password;



//         // try {
//         //     $sid = $_POST['sid'];
//         //     Session_Hanlder($sid);
//         //     echo json_encode(["message" => "Session is valid and active."]);
//         // } catch (Exception $e) {
//         //     http_response_code($e->getCode());
//         //     echo json_encode(["error" => $e->getMessage()]);
//         // }


//         // if (isset($_POST["sid"])) {
//         //     Session_Hanlder($_POST["sid"]);
//         // }

//         try {
//             $person = new Person($email);
//             $session_id = $person->authenticate($pass, $database->connection);
//             echo json_encode(["session_id" => $session_id, "message" => "Login successful"]);
//         } catch (Exception $e) {
//             http_response_code($e->getCode());
//             echo json_encode(["error" => $e->getMessage()]);
//         }
//     })
//     ->build();
