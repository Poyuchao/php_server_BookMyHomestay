<?
require_once 'utils/send-response.php';
require_once 'structures/Route.php';

$loginRoute= (new RouteBuilder())


    ->setMethod('POST')
    ->setPath('/login')
    ->setHandler(function ($params) {
        
    })
    ->build();
    echo "hi";


function login($UserEmail){
    $sql = "SELECT * FROM users WHERE email = '$UserEmail' ;" ;
    
}

