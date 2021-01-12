<?php
include "include.php";

$data = $_POST;
unset( $_POST );

$select = [
    'char_name',
    'char_number',
    'char_fac'
];

$where = [
    'player'    =>  $data[ 'id' ]
];

$return = select_sql( $select, "chars", $where );

json_return( $return );

?>