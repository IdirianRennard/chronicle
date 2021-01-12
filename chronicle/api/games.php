<?php
include 'include.php';

$in = convert( $_GET );

$select = [
    'pzo_btq',
    'name',
];

switch( $in->type ) {

    case 'ap' :
        array_push( $select, 'season', 'number' );
    break;

    case 'pfs' :
        array_push( $select, 'season', 'number', 'pfs_type', 'pfs_min_lv', 'pfs_max_lv', 'tag' );
    break;

    default:
    break;
}


$where = [
    'adv_type' => $in->type
];

$sql = select_sql( $select, 'pf2', $where );

json_return( $sql );

?>