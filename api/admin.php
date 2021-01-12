<?php
include 'include.php';

$data = $_POST;
unset( $_POST );

$sel = $data[ "sel" ];
unset( $data[ "sel" ] );

switch( $sel ) {

    case "add_admin_role" :
    break;

    case "add_admin_user" :

        switch( $data[ "add_user" ] ) {

            case "get_non-admin" :

                $select = [
                    "pzo_id",
                    "fname",
                    "lname",
                ];
                
                $admin_sql = select_sql( $select, "admins", NULL );
                $admin_list = [];
                
                for( $i = 0 ; $i < count( $admin_sql ) ; $i++ ) {
                    
                    $admin_list[ $admin_sql[ $i ][ "pzo_id"] ] = $admin_sql[ $i ][ "fname"] . " " . $admin_sql[ $i ][ "lname"];
                }

                $user_sql = select_sql( $select, "users", NULL );
                $user_list = [];

                for( $i = 0 ; $i < count( $user_sql ) ; $i++ ) {
                    
                    $user_list[ $user_sql[ $i ][ "pzo_id"] ] = $user_sql[ $i ][ "fname"] . " " . $user_sql[ $i ][ "lname"];
                }

                foreach( $admin_list as $k => $v ) {

                    unset( $user_list[ $k ] );
                }

                json_return( $user_list );

            break;

            default :

                $select = [
                    "pzo_id",
                    "fname",
                    "lname",
                ];

                $where = [
                    "pzo_id"    =>  $data[ "add_user" ],
                ];

                $user_sql = select_sql( $select, "users", $where );

                $values = [
                    "pzo_id"    =>  $data[ "add_user" ],
                    "fname"     =>  $user_sql[ 0 ][ "fname" ],
                    "lname"     =>  $user_sql[ 0 ][ "lname" ],
                    "add_admin" =>  false,
                    "mod_user"  =>  false,
                    "mod_sess"  =>  false,
                    "mod_coord" =>  false,
                    "reset_pwd" =>  false,
                ];

                for( $i = 0 ; $i < count( $data[ "user_perms"] ) ; $i++ ) {
                    $values[ $data[ "user_perms" ][ $i ] ] = true;
                }

                $return = insert_sql( $values, "admins" );

                json_return( $return );
            break;
        }

    break;

    case "mod_coord" :
        
        switch(  $data[ "coord" ] ) {

            case "chron_type" :
                $select = [ 
                    "format"
                ];

                $chron_type = select_sql( $select, "wh", NULL );

                json_return( $chron_type );
            break;

            case "list" :

                $select = [
                    "pzo_btq",
                    "pfs_type",
                    "season",
                    "number",
                    "name",
                ];
                
                $list_sql = select_sql( $select, "pf2", NULL );

                $return = [];

                for( $i = 0 ; $i < count( $list_sql ) ; $i++ ) {

                    if( isset( $return[ $list_sql[ $i ][ "pfs_type" ] ] ) ) {
                        
                    } else {

                        $return[ $list_sql[ $i ][ "pfs_type" ] ] = [];
                    }

                    $chron = new stdClass();
                    $chron->btq = $list_sql[ $i ][ "pzo_btq" ];

                    switch ( $list_sql[ $i ][ "pfs_type" ] ) {

                        case 'AP' : 
                            $name = "#";
                            if ( (int)$list_sql[ $i ][ "number" ] < 100 ) {

                                $name .= 0;

                                if ( (int)$list_sql[ $i ][ "number" ] < 10 ) { 

                                    $name .= 0 . (int)$list_sql[ $i ][ "number" ] ;

                                } else {

                                    $name .= (int)$list_sql[ $i ][ "number" ] ;
                                }
                            } else {

                                $name .= (int)$list_sql[ $i ][ "number" ] ;

                            }
                        break;

                        case 'bounty' :
                            $name = "#";
                            if ( (int)$list_sql[ $i ][ "number" ] < 10 ) {

                                $name .= 0 . (int)$list_sql[ $i ][ "number" ];

                            } else {

                                $name .= (int)$list_sql[ $i ][ "number" ];
                            }

                        break;

                        case 'quest' :
                            $name = "#";
                            if ( (int)$list_sql[ $i ][ "number" ] < 10 ) {

                                $name .= 0 . (int)$list_sql[ $i ][ "number" ];

                            } else {

                                $name .= (int)$list_sql[ $i ][ "number" ];
                            }
                        break; 

                        case 'scenario' :
                            $name = "";
                            if ( (int)$list_sql[ $i ][ "season" ] < 10 ) {

                                $name .= 0 . (int)$list_sql[ $i ][ "season" ];

                            } else {

                                $name .= (int)$list_sql[ $i ][ "season" ];
                            }

                            $name .= "-";
                            if ( (int)$list_sql[ $i ][ "number" ] < 10 ) {

                                $name .= 0 . (int)$list_sql[ $i ][ "number" ];

                            } else {

                                $name .= (int)$list_sql[ $i ][ "number" ];
                            }
                        break;

                        case 'mod' :
                            $name = "";
                        break;
                    }

                    $name .= " " . $list_sql[ $i ][ "name" ];
                    $chron->name = $name;
                                   
                    $return[ $list_sql[ $i ][ "pfs_type" ] ][] = $chron;
                }
                json_return( $return );

            break;

            case "submit" :
                $btq = $data[ "mod_coord" ];

                $update = $data;
                $return = [];

                foreach( $update as $k => $v ) {

                    $set = [];

                    if( strpos( $k, "_x" ) !== false || strpos( $k, "_y" ) !== false || strpos( $k, "ch_" ) !== false ) {                  
                                    
                        if( $k === "ch_format" && $v === "other" ) {

                            $set[ $k ] = "'" . $update[ "other_format" ] . "'";

                        } else{

                            $set[ $k ] = "'" . $v . "'";

                        }

                        $where = [
                            "pzo_btq"   =>  $btq,
                        ];
                        
                        $fix = update_sql( $set, "xy", $where );
                    }

                };

                if( isset( $update[ "other_format" ] ) ) {

                    insert_sql( $set, "wh" );
                }
                
                json_return( $return );
            break;

            case "xy" :

                $where = [
                    "pzo_btq" => $data[ "btq" ],
                ];

                $coords = select_sql( "*", "xy", $where );
                unset( $coords[ 0 ][ "pzo_btq" ] );

                json_return( $coords[ 0 ] );
            break;
        }
    break;

    case "mod_sess" :
    break;

    case "mod_user" :
    break;

    case "reset_pwd" :
    break;
}
?>