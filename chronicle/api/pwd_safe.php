<?php
include 'include.php';

$data = $_POST;
unset( $_POST );

$select = [
    'pzo_id',
    'valid',
    'exp_date',
];

$where = [
    'pzo_id'    =>  $data[ 'pzo_id' ],
    'hash'      =>  $data[ 'hash'   ],
];

$output = select_sql( $select, 'pwd_safety', $where );

json_return( $output );

?>