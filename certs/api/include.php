<?php
include '../include.php';
include '../f(x)/script.php';
include '../db/db_scr.php';

$time = gmdate( 'Y-M-d_H:i:s_T' );

class tab {
    public  $name;
    public  $short;
    public  $func;
};

class mail {
    public  $master;
    public  $user;
    public  $subject;
    public  $body;
}

class user {
    public  $fname;
    public  $lname;
    public  $email;
    public  $pzo_id;
}


header( 'Content-Type: application/json'        );
header( 'Access-Control-Allow-Methods: POST'    );
header( 'Access-Control-Allow-Origin: *'        );
?>