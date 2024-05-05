<?php
function sendHttp_Code($code, $msg, $die_flag = false)
{
    http_response_code($code);
    if ($die_flag) {
        die($msg);
    } else {
        echo ($msg);
    }
}
function check_key($keys, $sourceData)
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $sourceData)) {
            throw new Exception("Invalid keys", 400);
        }
    }
}
function Session_Hanlder($sid)
{
    session_id($sid);
    session_start();
    if (isset($_SESSION["time_out"]) && $_SESSION["time_out"] > time()) {  //if not session timeout
        $_SESSION["time_out"] = time() + TIME_OUT;
    } else {  //if already session timeout
        session_unset();
        session_destroy();
        throw new Exception("Session timed out/does not exist.", 408);
    }
}
