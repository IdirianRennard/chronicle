<?php
include 'include.php';

$return = new stdClass();
$perm = $_POST[ "curl" ];

$where = [
    "pzo_id"    => $_POST[ "golem" ],
];

$may_i = select_sql( $perm, "admins", $where );

//$return->may_i = (bool)$may_i[0][ $perm ];

if ( $may_i ) {

    $where = [
        "role"  =>  $perm,
    ];

    $tasks = select_sql( "*", "tasks", $where );
}

$return->tasks = $tasks;

json_return( $return );

?>