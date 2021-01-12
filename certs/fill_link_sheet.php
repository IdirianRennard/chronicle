<?php
//include files
include 'include.php';
include './db/db_scr.php';
require __DIR__ . '/vendor/autoload.php';

//include all inclusion files
include_fofx();

$client = new Google_Client();
$client->setApplicationName( 'Chronicile' );
$client->setScopes( [ Google_Service_Sheets::SPREADSHEETS ] );
$client->setAccessType( 'offline' );
$client->setAuthConfig( __DIR__ . '/credentials.json' );

$service = new Google_Service_Sheets( $client );

$link = "https://docs.google.com/spreadsheets/d/14L2BcQXkfIfh6Gjbw763NC2LrwnqD4jDhUKmj4xNIZM/edit?usp=sharing";

$explode = explode( '/', $link );
$sheetID = $explode[ 5 ];

$select = [
    "name",
    "season",
    "number",
    "pfs_max_lv",
    "tag",
];

$games = select_sql( $select, "pf2", NULL );

$range = "game_opt!A2:D";

$valueInputOption = "RAW";

$values = [];

for( $i = 0 ; $i < count( $games ) ; $i++ ) {

    if( is_numeric( $games[ $i ][ "season" ] ) ) {

        if( $games[ $i ][ "season"] < 10 ) {
            $game_no = "0" . $games[ $i ][ "season"];
        } else {
            $game_no = $games[ $i ][ "season"];
        }

        if ( $games[ $i ][ "number" ] < 10 ) {
            $game_no .= "-0" . $games[ $i ][ "number" ];
        } else {
            $game_no .= "-" . $games[ $i ][ "number" ];
        }

        if( strpos( $games[ $i ][ "name" ], "[" ) !== FALSE ) {

            $game_no .= " " . substr( $games[ $i ][ "name" ], strpos( $games[ $i ][ "name" ], "[" ) );
        }

    } else {

        if( $games[ $i ][ "season" ] === "M" ) {

            $game_no = clean_text( $games[ $i ][ "name" ] );

        } else {

            $game_no = clean_text( $games[ $i ][ "season" ] );

            if ( $games[ $i ][ "number" ] < 10 ) {
                $game_no .= "-0" . $games[ $i ][ "number" ];
            } else {
                $game_no .= "-" . $games[ $i ][ "number" ];
            }

        }
    }

    if( strpos( $games[ $i ][ "tag" ], "Faction" ) !== FALSE ) {

        $faction = substr( $games[ $i ][ "tag" ], strpos( $games[ $i ][ "tag" ], "(" ) +1, -1 );
    } else {

        $faction = "";
    }

    $array = [
        $game_no,
        $games[ $i ][ "name" ],
        $games[ $i ][ "pfs_max_lv" ],
        $faction
    ];

    $values[] = $array;
}

print_r( $values );

$body = new Google_Service_Sheets_ValueRange( [
    'values' => $values
] );

$params = [
    'valueInputOption' => $valueInputOption
];

$result = $service->spreadsheets_values->update(
    $sheetID, 
    $range,
    $body, 
    $params
);
?>