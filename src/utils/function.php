<?php

// function Session_Hanlder($sid)
// {
//     session_id($sid);
//     session_start();
//     if (isset($_SESSION["time_out"]) && $_SESSION["time_out"] > time()) {  //if not session timeout
//         $_SESSION["time_out"] = time() + TIME_OUT;
//     } else {  //if already session timeout
//         session_unset();
//         session_destroy();
//         throw new Exception("Session timed out/does not exist.", 408);
//     }
// }



function Audit_generator($eventType,$outcome,$desc,$userEmail=""){
    $aduit = date("Y-m-d H:i:s ",$_SERVER["REQUEST_TIME"]).$_SERVER["REMOTE_ADDR"].":".$_SERVER["REMOTE_PORT"]." $userEmail $eventType $outcome $desc \n";
    $file = new File(DATA_ROUTE."/audit");
    $file->writeFile("Audit ".date("Ymd").".txt",$aduit);
}