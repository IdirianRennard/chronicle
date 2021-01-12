<?php
use setasign\Fpdi\Fpdi;
require "../vendor/autoload.php";

require_once('../fpdf/fpdf.php'); 
require_once( '../fpdf/wordwrap.php' );
require_once('../fpdi/src/autoload.php'); 

//include files
include 'include.php';
include './db/db_scr.php';

$players = [];

$return = new stdClass();

$event = [ 
    "name"      =>  $_POST[ "gm_e_name" ],
    "code"      =>  $_POST[ "gm_e_code" ],
    "date"      =>  $_POST[ "gm_e_date" ],
    "played"    =>  substr( $_POST[ "game_name" ], 0, strpos( $_POST[ "game_name" ], "(" ) - 1 ),
    "rep"       =>  $_POST[ "s_rep" ],
    "b_rep_fac" =>  $_POST[ "s_b_rep_menu_sel" ],
    "b_rep"     =>  $_POST[ "s_b_rep_sel" ],
    "gm"        =>  $_POST[ "gm_name" ],
    "gm_id"     =>  $_POST[ "gm_pzo_id" ],
    "player_ct" =>  $_POST[ "s_no_players" ],
    "treasure"  =>  $_POST[ "s_tb" ],
];

if( $_POST[ "tier_low" ] === "true" ) {
    $event[ "tier" ] = "low";
}

if( $_POST[ "tier_high" ] === "true" ) {
    $event[ "tier" ] = "high";
}

foreach( $_POST as $k => $v ) {

    if(  substr( $k, 0, 2 ) === "p_" || strpos( $k, "gm_char" ) !== FALSE ) {
        $players[ $k ] = $v;
    }

}

//Get Chronicle Info
$chronicle = get_chronicle( $event );
$users = [];

$return->type = "web_service";
$return->event = $event;
$return->chronicle = $chronicle;
$return->players = $players;

for( $i = 1 ; $i <= $event[ "player_ct" ] ; $i++ ) {
    $users[ $i ] = parse_web_submit_user( $i, $return );
}

if( $_POST[ "gm_cert"] === "true" ) {
    $users[] = parse_web_submit_user( "gm", $return );
}
$return->certs = $users;

print_r( $return );
?>