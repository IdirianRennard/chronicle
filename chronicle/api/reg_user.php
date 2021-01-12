<?php
include 'include.php';

if (  isset( $_GET[ 'f' ] ) ) {

    switch ( $_GET[ 'f' ] ) {
        
        case 'check':
            
            $check = $_GET;
            unset( $check[ 'f' ] );

            $select = [
                'fname',
                'lname',
                'email',
                'pzo_id'
            ];

            $where = [];

            foreach( $check as $k => $v ) {
                $where[ $k ] = $v;
            }

            json_return( select_sql( $select, 'users', $where ) );
            break;
        
        case 'submit':
            
            $where = [];
            $where[ 'reg_date' ] = $time;
            $where[ 'pzo_id' ] = $_GET[ 'pzo_id' ];
            $where[ 'pronoun' ] = NULL;
            $where[ 'fname' ] = $_GET[ 'fname' ];
            $where[ 'lname' ] = $_GET[ 'lname' ];
            $where[ 'email' ] = urldecode( $_GET[ 'email' ] );
            $where[ 'hash' ] = $_GET[ 'hash' ];

            $user = insert_sql( $where, 'users' );

            $pwd_db = [];
            $pwd_db[ 'pzo_id'   ] = $_GET[ 'pzo_id' ];
            $pwd_db[ 'hash'     ] = $_GET[ 'hash' ];
            $pwd_db[ 'reg_date' ] = $time;
            $pwd_db[ 'exp_date' ] = gmdate( 'Y-M-d_H:i:s_T', time() + ( 86400 * 180 ) );
            $pwd_db[ 'valid'    ] = true;

            $pwd = insert_sql( $pwd_db, 'pwd_safety' );

            $email_db = [];
            $email_db[ 'pzo_id' ] = $_GET[ 'pzo_id' ];
            $email_db[ 'email'  ] = urldecode( $_GET[ 'email' ] );
            $email_db[ 'confirmed' ] = 'false';

            $pwd = insert_sql( $email_db, 'confirmed_email' );

            $return = new stdClass();

            $return->user = $user;
            $return->pwd = $pwd_db;
            $return->email = $email_db;
            
            json_return( $return );
            break;
        
        default:
            json_return( $_GET );
            break;
    }
} else {
    json_return( $_GET );
}

?>