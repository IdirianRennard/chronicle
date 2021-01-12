<?php

//log data to js console using php
function console( $data ) { 

	if ( is_string( $data ) ) {

		//include the qutoes
		$json = "'$data'";

	} else {

		//make it json to show in the console
		$json = make_json( $data );
	}
	
	echo "<script>console.log( $json )</script>\n";
}

//convert array to object
function convert( $array ) {

	if ( is_array( $array ) ) {

		$json = json_encode( $array );

		$return = json_decode( $json );

		return $return;

	} else { 

		return "Error, array not passed to function.";
	}
}

//echo pretty json
function echo_json( $data ) {

	//make it json
	$return = make_json( $data );

	//make it so the json echoes pretty within html
	$return = "<pre>$return</pre>";

	echo $return;
}

//echo pretty json
function json_echo( $data ) {

	//make it json
	$return = make_json( $data );

	//make it so the json echoes pretty within html
	$return = "$return";

	echo $return;
}

//return json data from sql result
function json_return( $data ) {

	//make it json
	$return = make_json( $data );

	echo $return;
}

//make json with pretty print for a shorter key stroke
function make_json ( $data ) {

	//if data is a string, return the string
	if ( is_string( $data ) ) {
		return $data;
	
	//else encode the data
	} else {
		$return = json_encode( $data, JSON_PRETTY_PRINT );
	}
	
	return $return;
}

//write email
function write_mail ( $obj ) {

	$headers = "From: ASHER <asher@houserennard.online> \r\n";
	
	if ( $obj->master ) {

		$to = "Idirian Rennard <idirian@houserennard.online>";

		$headers .= "Reply-To: " . $obj->user->fname . " " . $obj->user->lname . "<" . $obj->user->email . "> \r\n";
		$headers .= "CC: " . $obj->user->fname . " " . $obj->user->lname . "<" . $obj->user->email . "> \r\n";

	} else {

		$to = $obj->user['fname'] . " " . $obj->user['lname'] . "<" . $obj->user['email'] . ">";

		$headers .= "Reply-To: Idirian Rennard <idirian@houserennard.online> \r\n";
	}
	
	$headers .= "MIME-Versio : 1.0 \r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1 \r\n";

	$body = wordwrap( $obj->body, 70 , "\r\n");

	$return = new stdClass();
	$return->headers = $headers;
	$return->to = $to;
	$return->subject = $obj->subject;
	$return->body = $body;
	$return->success = mail( $to, $obj->subject, $body, $headers );

	return $return;
}

?>