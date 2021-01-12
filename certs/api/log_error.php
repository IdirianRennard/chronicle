<?php
include 'include.php';

$output = new stdClass();

$endpoint = base64_decode( $_GET[ 'endpoint'] );

$query = substr( $endpoint, strpos($endpoint, "?") + 1 );

$endpoint = explode( "?", $endpoint, 2 );
$endpoint = $endpoint[0];

parse_str( $query, $query );

$result = json_decode( base64_decode(  $_GET[ 'result'] ) );

$output->email = $_GET[ 'user' ];

$select = [ 
    'fname',
    'lname',
    'pzo_id',
];

$where = [
    'email' => $output->email,
];

$user = select_sql( $select, 'users', $where );

if ( count( $user ) === 0 ) {

    $user = new stdClass();

    $user->fname = "Unknown";
    $user->lname = "Unknown";
    $user->pzo_id = "Unknown";
} 

$output->user = $user;
$output->title = $_GET[ 'title' ];
$output->error = $_GET[ 'error' ];
$output->call = $_GET[ 'call' ];
$output->time = $time;
$output->endpoint = $endpoint;
$output->query = $query;
$output->result = $result;

$link = "../logs/" . $output->time . "_" . $output->email . "_" . $output->error . ".json";

//echo "$link\n\n";

$fp = fopen( $link, "w" );
fwrite( $fp, json_return( $output ) );
fclose( $fp );

$to = 'idirian@houserennard.online';

$subject = 'Chronicle Error @ ' . $output->time;

$headers = "From: ASHER <asher@houserennard.online> \r\n";
$headers .= "Reply-To: " . "" . "\r\n";
$headers .= "CC: " . $output->user . "\r\n";
$headers .= "MIME-Versio : 1.0 \r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1 \r\n";

?>