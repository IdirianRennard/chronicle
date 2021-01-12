<?php
	header( 'Content-Type: application/json'        );
	header( 'Access-Control-Allow-Methods: POST'    );
	header( 'Access-Control-Allow-Origin: *'        );

include 'globals.php';

$std_font = "Times";
$sig_font = "Maghrib";
$sig_size = 24;	
$std_size = 12;	
$sm_size = 8;

//run all user created functions that include other directories
function all_includes() {

	//get all the functions
	$defined = get_defined_functions ();

	//loop through defined[ 'user' ] array to get names for include and add to includes array
	foreach( $defined[ 'user' ] as $item ) {
		
		//find all of the include functions
		if ( substr( $item, 0, 8 ) === 'include_' ) {

			//run include functions
			call_user_func( "$item" );
		}
	}
}

//clear un-needed data from dir_data()
function clean_dir( $obj ) {

	//set return value as array
	$return = [];

	//check to see if the file type is included, remove what isn't 
	foreach( $obj->array as $item ){ 
		if ( strpos( $item, "." . $obj->ftype ) ) {
			$return[] = $item;
		}
	}

	//return the cleaned array
	return $return;
}

//get directory info 
function dir_data( $dir, $ftype ) { 
	
	//make return object
	$data = new stdClass();

	//file type to search for later
	$data->ftype = $ftype;

	//scan the directory and add the array	
	if ( is_array( scandir( $dir ) ) ) {
		$data->array = scandir( $dir );
	}

    //return the clean dir_data
	$return = clean_dir( $data );

	return $return;
};

//get user ip
function get_user_ip(){

    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//get include directories
function get_inc_dir( $folder ) {

	//get the specific folder name we are using to shorten the dialog
	$last = substr( $folder, -1) . "/";
	
	//get the filepath from URL
	$link = getcwd();

	if ( strpos( $link, $folder ) ) {

		$dir = "../$folder/";

	} else {

		echo "An error occured! \n The selected folder does not appear.";
	}	
	
	$ctr = 10;
	$i = 0;

    while ( !is_dir( $dir ) && $i <= $ctr ) {

		$dir = "../$dir";
		$i++;
    }
    
    return $dir;
}

//include everything in css folder
function include_css() {

    $dir = "../" . $GLOBALS[ 'IFP' ] . '/styles';

	//get directory data 
	$list = dir_data( $dir, 'css' );

    //load the css files
	foreach ( $list as $item ) {
		echo "<link href='$dir/$item' rel='stylesheet'>\n";
	}
};

//include everything in f(x) folder
function include_fofx() {

	$dir = "../" . $GLOBALS[ 'IFP' ] . "/f(x)";
	
	//get directory data
	$list = dir_data( $dir, 'php' );

	//loop through array, add the files
	foreach( $list as $item ) {
		include "$dir/$item";
	}		
};

//read every file in js folder
function include_js() {

    $dir = "../" . $GLOBALS[ 'IFP' ]. '/js';

	//get directory data
	$list = dir_data( $dir, 'js' );

	//create the location for the scripts if some need to be added later
	echo "<div id='js_scripts'>";

	//loop through array
	foreach( $list as $item ) {
		if ( substr( $item, 0, 2 ) === '$$' ) {

		} else {
			if ( substr( $item, 0, strlen( 'jquery' ) ) === 'jquery' ) {
			
				$hc_dir = "https://houserennard.online" . substr( $dir, 2 );
				echo "<script src='$hc_dir/$item'></script>\n";
				
			} else {
				echo "<script src='$dir/$item'></script>\n";
			}
		}
	}		

	//end the location for js scripts
	echo "</div>";
};


?>