<?php
include 'include.php';

date_default_timezone_set("UTC");

$where = [
    "time"  =>  time(),
    "rec"   =>  $_POST,
];

insert_sql( $where, "logs" );
?>