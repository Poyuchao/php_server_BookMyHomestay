<?php

function Audit_generator($eventType, $outcome, $desc, $userEmail = "")
{
    $aduit = date("Y-m-d H:i:s ", $_SERVER["REQUEST_TIME"]) . $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"] . " $userEmail $eventType $outcome $desc \n";
    $file = new File(ROOT . "data/audit");
    $file->writeFile("Audit " . date("Ymd") . ".txt", $aduit);
}
