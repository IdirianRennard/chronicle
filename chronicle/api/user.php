<?php
include 'include.php';

$output = new stdClass ();

$auth = [];

if( isset( $_POST['email' ] ) ) {
    $auth[ 'email' ] = urldecode( $_POST[ 'email' ] );
}

if( isset( $_POST['hash'] ) ) {
    $auth[ 'hash' ] = $_POST[ 'hash' ];
}

$select = [
    'reg_date',
    'pzo_id',
    'pronoun',
    'fname',
    'lname',
    'email'
];

$return = select_sql( $select, 'users', $auth );

json_return( $return );

/*$error_test = [ 
    'zero',
    'one',
    'two',
    'three',
];

json_return( $error_test );*/
?>