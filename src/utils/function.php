<?php

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