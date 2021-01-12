<?php
include 'include.php';

$data = $_POST;

$select = [
    "pzo_id",
    "add_admin",
    "change_user_pwd",
    "mod_sess",
    "mod_user",
    "mod_coord"
];

$where = [
    "pzo_id"    =>  $data[ 'pzo_id' ],
];

$perm = select_sql( "*", "admins", $where );

$json = [];

if ( count( $perm ) > 0 ) {
    
    $perm[0][ "id" ] = $data[ "pzo_id" ];
    $permkey = http_build_query( $perm[0] );
    $permkey = urlencode( $permkey );
    $permkey = base64_encode( $permkey );
    $permkey = str_rot13( $permkey );
    
    array_push( $json, $permkey );

    json_return( $json );

} else {
    json_return( $perm );
}
?>