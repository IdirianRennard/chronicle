<?php

function zip_certs( $game_folder ) {

    //Zip Folder
	//Initialize archive object
	$zip = new ZipArchive();
    $zip_name = "./zip/$game_folder.zip";
    $zip_folder = "./zip/$game_folder/";

    if( $zip->open( $zip_name, ZIPARCHIVE::CREATE ) !== TRUE ){

        return "CANNOT CREATE ZIP FILE AT THIS TIME.";
    
    };

    $files = scandir( $zip_folder );
   
    unset( $files[0] );
    unset( $files[1] );

    $files = array_values( $files );

        foreach( $files as $f ){

        $zip->addFile( "$zip_folder$f", $f );
    }


    // Zip archive will be created only after closing object
	$zip->close();

	if ( file_exists( $zip_name ) ) {

        return TRUE;
	}

}

function zip_link( $file_list, $file_folder ) {

    $zip = new ZipArchive();
    $zip_name = "./zip/$file_folder.zip";
    $zip_folder = "./zip/$file_folder/";
    
    if( $zip->open( $zip_name, ZIPARCHIVE::CREATE ) !== TRUE ){

        return FALSE;
    
    };

    for( $i = 0 ; $i < count( $file_list ) ; $i++ ) {

        $zip->addFile( $zip_folder . $file_list[ $i ], $file_list[ $i ] );
    }

    $zip->close();

    if ( file_exists( $zip_name ) ) {

        return TRUE;

	}
}
?>