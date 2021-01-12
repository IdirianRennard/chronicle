<?php
require __DIR__ . '/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

//include files
include 'include.php';
include './db/db_scr.php';

require_once('fpdf/methos.php');

	//include all inclusion files
	include_fofx();

$data = $_POST;
$return = new stdClass();

switch ( $data[ 'submit_type' ] ) {

    case 'link' :

        $client = new Google_Client();
        $client->setApplicationName( 'Chronicile' );
        $client->setScopes( [ Google_Service_Sheets::SPREADSHEETS ] );
        $client->setAccessType( 'offline' );
        $client->setAuthConfig( __DIR__ . '/credentials.json' );

        $service = new Google_Service_Sheets( $client );

        $link = base64_decode(  $data[ 'upl_link' ] );
        $link = urldecode( $link );

        $explode = explode( '/', $link );
        $sheetID = $explode[ 5 ];

        //Game Info
        $range = "PFS2e!A1:P7";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $game_info = $sheet_data->getValues();

        //Test Game Info to see if Legal for submission:
        if( strpos( $game_info[ 0 ][ 1 ], "<< EVENT NAME >>" ) !== FALSE ) {

            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Event Name is not filled in.";
            $error->link = $link;
            $error->game_data = $game_info;

            json_return( $error );
            exit;
        }

        if( strpos( $game_info[ 0 ][ 4 ], "<<EVENT CODE GOES HERE>>" ) !== FALSE ) {

            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Event Code is not filled in.";
            $error->link = $link;
            $error->game_data = $game_info;

            json_return( $error );
            exit;
        }

        if( strpos( $game_info[ 1 ][ 1 ], "Session-Name" ) !== FALSE ) {

            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Chronicle Not Selected";
            $error->link = $link;
            $error->game_data = $game_info;

            json_return( $error );
            exit;
        }

        if( strpos( $game_info[ 1 ][ 4 ], "<<Time goes here>>" ) !== FALSE ) {

            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Event Date is not filled in.";
            $error->link = $link;
            $error->game_data = $game_info;

            json_return( $error );
            exit;
        }

        if( $game_info[ 3 ][ 1 ] === "" ) {

            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Reputation Gained is not filled in.";
            $error->link = $link;
            $error->game_data = $game_info;

            json_return( $error );
            exit;
        }

        //Treasure Bundles
        $range = "PFS2e!P7:P7";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $treasure_bundles = $sheet_data->getValues();
        if( $game_data[ 6 ][ 15 ] === "" ) {
            
            $error = new stdClass();
            $error->result = "ERROR";
            $error->msg = "Treasure Bundles are not filled in.";
            $error->link = $link;

            json_return( $error );
            exit;
        } else {
            $tb = (float)$game_data[ 6 ][ 15 ];
        }
        

        //Player Info
        $range = "PFS2e!A11:R16";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $players = $sheet_data->getValues();

        //Player Downtime Info
        $range = "PFS2e!A27:R32";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $pl_dt = $sheet_data->getValues();

        //Add Downtime to Players;
        for( $i = 0 ; $i < count( $players ) ; $i++ ) {
            for( $c = 0 ; $c < count( $pl_dt[ $i ] ) ; $c++ ) {
                array_push( $players[ $i ], $pl_dt[ $i ][ $c ] );
            }
        }

        //GM Data
        $range = "PFS2e!A10:R10";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $gm_data = $sheet_data->getValues();
        $gm = $gm_data[ 0 ];

        $time = strtotime( $game_info[ 1 ][ 4 ] );
        $date = date( "d M Y", $time );

        $event = [ 
            "name"      =>  $game_info[ 0 ][ 1 ],
            "code"      =>  $game_info[ 0 ][ 4 ],
            "date"      =>  $date,
            "played"    =>  $game_info[ 1 ][ 2 ],
            "rep"       =>  (int)$game_info[ 3 ][ 1 ],
            "b_rep_fac" =>  $game_info[ 4 ][ 1 ],
            "b_rep"     =>  (int)$game_info[ 5 ][ 1 ],
            "gm"        =>  $gm[ 2 ],
            "gm_id"     =>  $gm[ 4 ],
            "player_ct" =>  $game_info[ 5 ][ 6 ],
            "challenge" =>  $game_info[ 6 ][ 4 ],
            "treasure"  =>  $tb,
        ]; 
       
        //Get Chronicle Info
        $chronicle = get_chronicle( $event );
        
        $range = "Discord Settings!B2:B10";
        $sheet_data = $service->spreadsheets_values->get( $sheetID, $range );
        $discord = $sheet_data->getValues();
        $disc = [
            "webhook"   =>  $discord[ 0 ][ 0 ],
            "bot_name"  =>  $discord[ 1 ][ 0 ],
            "avatar"    =>  $discord[ 2 ][ 0 ],
            "text"      =>  $discord[ 3 ][ 0 ],
        ];

        $cp = $event[ "challenge" ];
        $player_ct = $event[ "player_ct" ];
    
        if(  $chronicle[ "adv_type" ] === "pfs" && $chronicle[ "season" ] !== "b" ) {
    
            if ( $cp <= 15 ) {
                $event[ "tier" ] = "low";
            }
    
            if( $cp >= 19 ) {
                $event[ "tier" ] = "high";
            }
    
            if( $cp >= 16 && $cp <= 18 && $player_ct <= 4 ) {
                $event[ "tier" ] = "high";
            }
    
            if( $cp >= 16 && $cp <= 18 && $player_ct >= 5 ) {
                $event[ "tier" ] = "low";
            }
        }

        $return->font = [
            "std"   =>  "Times",
            "sig"   =>  "Maghrib",
            "size"  =>  12,
            "sm"    =>  8,
        ];
        
        $return->type = 'link'; 
        $return->link = $link;
        $return->id = $sheetID; 
        $return->event = $event;
        $return->gm = $gm;
        $return->players = $players;
        $return->chronicle = $chronicle;
        $return->disc = $disc;
    break;

    case 'upload' :
    break;

    default : 
    break;
}

$certs = [];

for ( $i = 0 ; $i < $event[ "player_ct" ] ; $i++ ) {

    $certs[ $i ] = parse_user( $players[ $i ], $return, false );
}
if ( $gm[ 3 ] !== "" ) {

    $certs[] = parse_user( $gm, $return, true );
}

$return->certs = $certs;

mkdir( "./zip/" . $chronicle[ "game_folder" ], 0777, true );

$created = [];

for ( $i = 0 ; $i < count( $certs ) ; $i++ ) {

    
    $user = $certs[ $i ];
    $dest = "./zip/" . $chronicle[ "game_folder" ] . "/" . $user[ "file_name" ];

    $pdf = new PDF ();

    create_pdf( $pdf, $return );
    write_player_info( $pdf, $return, $user );
    write_gained_info( $pdf, $return, $user );
    write_tier_x( $pdf, $return );
    write_downtime_info( $pdf, $return, $user );
    write_shopping_info( $pdf, $return, $user );
    write_gm_block( $pdf, $return );

    $pdf->Output( "F", $dest );

    $created[] = $user[ "file_name" ];

    if( $disc[ "webhook" ] === NULL ) {

        if( zip_link( $created, $chronicle[ "game_folder" ] ) ) {

            $return->result = "SUCCESS";
            $return->ftp = "https://houserennard.online/certs/zip/" . $chronicle[ "game_folder" ] . ".zip";
    
        }

    } else {

        $send = new stdClass();
        $file = $dest;
        $send->file_url = $file;
        $send->status = send_chronicle_webhook( $return, $user, $dest );
        $return->disc[] = $send;
        $return->result = "DISCORD";
    }

}

$return->created = $created;

json_return( $return );

?>

