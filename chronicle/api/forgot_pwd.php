<?php
include 'include.php';

if ( isset( $_GET[ 'f' ] ) ){

    $select = [
        'fname',
        'lname',
        'email',
        'pzo_id',
        'hash'
    ];

    $where = [
        'email' => urldecode( $_GET[ 'email' ] ),
    ];

    $result = select_sql( $select, 'users', $where );

    $user = $result[0];

    $expire = gmdate( 'Y-M-d_H:i:s_T', time() + ( 1000 * 60 * 60 ) );

    $invalid_pwd = [
        'valid' => false
    ];

    $where_pwd = [
        'pzo_id' => $user['pzo_id'],
        'hash'  =>  $user['hash'],
    ];

    $invalidate_pwd = update_sql( $invalid_pwd, 'pwd_safety', $where_pwd );

    $mail = new mail();
    $mail->master = false;
    $mail->user = $user;
    $mail->subject = "Chronicle Password Reset Request for : " . $user['pzo_id'] . " - " . $user['fname'] . " " . $user['lname'];
            
    $user[ 'expire' ] = $expire;

    $d = base64_encode( http_build_query( $user ) );

    $link = "https://houserennard.online/chronicle/?$d";

    $body = "<html><body>";
    $body .= "Hello " . $user['fname'] . ",";
    $body .= "<br><br>My name is ASHER. I'm an artificual (semi-)Intelligence, and here to help! I understand you are having difficulty logging in.<br><br>";
    $body .= "I'd like to help you reset your password so you can regain access to your account.";
    $body .= "<br><br>By clicking on the link below, or by copying it to a browser, you will be directed to a portal where you can reset your password.";
    $body .= "<br><br>To reset your password, click <a href='$link' target='blank'>here</a> or copy the address below: <br><br>$link<br><br>";
    $body .= "A few things to note:<ul>";
    $body .= "<li>This link will expire 1 hour after it was generated.</li>";
    $body .= "<li>After your password has been updated, this link will no longer be valid.</li>";
    $body .= "</ul>";
    $body .= "If you believe you have received this message in error, or need more help, simply reply to this email and Master Idirian will look into the issue.<br><br>";
    $body .= "Have a great day! <br>";
    $body .= "ASHER</body></html>";

    $mail->body = $body;

    json_return( write_mail( $mail ) );
}

if( isset( $_GET[ 'n' ] ) ) {

    $new_pwd = [
        'hash' => $_GET['hash'],
    ];

    $user_data = [
        'pzo_id' => $_GET[ 'pzo_id' ],
        'email' => urldecode( $_GET[ 'email' ] ),
    ];

    $users = update_sql( $new_pwd, 'users', $user_data );

    $output = new stdClass();
    $output->users = $users;

    $insert = [
        'pzo_id'    =>  $_GET[ 'pzo_id' ],
        'hash'      =>  $_GET[ 'hash'   ],
        'reg_date'  =>  $time,
        'exp_date'  =>  gmdate( 'Y-M-d_H:i:s_T', time() + ( 86400 * 180 ) ),
        'valid'     =>  true,
    ];

    $pwd_safety = insert_sql( $insert, 'pwd_safety' );

    $output->pwd_safety = $pwd_safety;

    json_return( $output );
}
?>


