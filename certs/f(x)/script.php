<?php

function call( $data, $url ) {

	$header = [
		"Content-Type:application/json",
	];

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	
	$response = curl_exec( $ch );
	
	curl_close( $ch );

	return $response;
}

function clean_text( $string ) {
	$word = $string;
  
	$word = str_replace( '-' , ' | ', $word );
	$word = str_replace( '_' , ' ', $word );
  
	$word = ucwords( $word );
  
	$word = str_replace( ' | ', '-', $word );
	$word = str_replace( "_", " ", $word );
  
	$word = str_replace( 'And', 'and', $word );
	$word = str_replace( 'Of', 'of', $word );
	$word = str_replace( 'The', 'the', $word );
	$word = str_replace( 'In', 'in', $word );

	$word = ucfirst( $word );
	
	return $word;
  }

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

//create pdf base
function create_pdf( $pdf, $obj ) {

	$chronicle = $obj->chronicle;
	
	$file_loc = $chronicle[ "file_loc" ];

	$pdf->AddPage();
    $pdf->setSourceFile( $file_loc );
 
	$template = $pdf->importPage(1);
    $pdf->useTemplate( $template );
	
	$pdf->SetAutoPageBreak(false,0);
    $pdf->SetMargins( 0, 0, 0 );
}

function dt_check() {

	$args = func_get_args();

	$activity = $args[ 0 ];
	$check = (int)$args[ 1 ];
	$dc = (int)$args[ 2 ];

	if( $check < $dc ) {

		if ( $check <= ( $dc - 10 ) ) {

			//CRITICAL FAILURE!
			$val = 0;

		} else {
						
			//FAILURE!
			$val = 1;
		}
	}	

	if ( $check >= $dc ) {
					
		if ( $check >= ( $dc + 10 ) ){
						
			//CRITICAL SUCCESS!
			$val = 3;

		} else {

			//SUCCESS!
			$val = 2;
		}
	}

	if ( isset( $args[ 3 ] ) ) {
		$nat1 = filter_var( $args[ 3 ], FILTER_VALIDATE_BOOLEAN );	
	}
	
	if ( isset( $args[ 3 ] ) ) {
		$nat20 = filter_var( $args[ 4 ], FILTER_VALIDATE_BOOLEAN );
	}	

	if( $nat1 ) {
		$val = max( 0, ( $val - 1 ) );
	}

	if( $nat20 ) {
		$val = min( 3, ( $val + 1 ) );
	}


	return $val;
}

//echo pretty json
function echo_json( $data ) {

	//make it json
	$return = make_json( $data );

	//make it so the json echoes pretty within html
	$return = "<pre>$return</pre>";

	echo $return;
}

function get_chronicle( $event ) {
	//get btq#
	$where = [
		'name'  => $event[ "played" ],
	];

	$chronicle = select_sql( "*", "pf2", $where );
	$chronicle = $chronicle[ 0 ];

	$btq = $chronicle[ 'pzo_btq' ];

	//get coordinates
	$where = [
		'pzo_btq' => $btq
	];

	$coords = select_sql( "*", "xy", $where );
	$chronicle[ "xy" ] = $coords[ 0 ];
	unset( $chronicle[ "xy" ][ "pzo_btq" ] );

	//get wh
	$where = [
		"format" => $chronicle[ "xy" ][ "ch_format" ],
	];

	$wh = select_sql( "*", "wh", $where );

	$chronicle[ "wh" ] = $wh[ 0 ];

	unset( $chronicle[ "xy" ][ "ch_format" ] );

	$file_loc = "./pf2/";

	switch ( $chronicle[ 'adv_type' ] ) {

		case 'mod' :
			$xp = 12;
			$file_loc .= "saa/$btq.pdf";
		break;

		case 'ap':
			$xp = 12;
			$file_loc .= $chronicle[ 'adv_type' ] . "/" . strtolower( $chronicle[ 'season' ] ) . "/$btq.pdf";
		break;

		case 'pfs' :
			$file_loc .= $chronicle[ 'adv_type' ] . "/" . strtolower( $chronicle[ 'season' ] ) . "/$btq.pdf";

			switch( strtolower( $chronicle[ 'season' ] ) ) {

				case 'q' :
					$xp = 1;
					$event[ "treasure" ] = 2.5;
				break;

				case 'b' :
					$xp = 1;
				break;

				default :
					$xp = 4;
				break;
			}

		break;
	}

	$chronicle[ "file_loc" ] = $file_loc;

	$game_folder = $event[ "code" ] . "-" . $event[ "played" ] . "-" . $event[ "date" ];
	$chronicle[ "game_folder" ] = $game_folder; 
	$chronicle[ 'xp' ] = $xp;
	
	return $chronicle;
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

function parse_user( $user, $obj, $gm_bool ) {

	$event = $obj->event;
	$chronicle = $obj->chronicle;
	$disc_text = $obj->disc[ "text" ];

	$replace = [
		"%name"			=>	$user[ 2 ],
		"%character"	=>	$user[ 3 ],
		"%player_num"	=>	$user[ 4 ],
		"%char_num"		=>	$user[ 6 ],
	];

	foreach ( $replace as $key => $val ) {
		$disc_text = str_replace(  $key, $val, $disc_text );
	}

    $pl = [
		"name" 			=>	$user[ 2 ],
		"char"			=> 	$user[ 3 ],
		"pzo_id"		=>	$user[ 4 ],
		"char_no"		=>	$user[ 6 ],
		"faction"		=>	$user[ 7 ],
		"level"			=>	(int)$user[ 8 ],
		"purchase"		=>	$user[ 11 ],
		"purchase_gp"	=>	number_format( $user[ 12 ], 2, ".", "," ),
		"sold"			=>	$user[ 13 ],
		"sold_gp"		=>	number_format( $user[ 14 ], 2, ".", "," ),
		"slow"			=>	$user[ 10 ],
		"gp"			=>	$user[ 15 ],
		"died"			=>	$user[ 16 ],
		"file_name"		=>	$user[ 4 ] . "-" . $user[ 2 ] . "-" . $event[ "played" ] . "-" . $event[ "date" ] . ".pdf",
		"add_notes"		=>	$user[ 17 ],
		"username"		=>	$user[ 1 ],
		"disc"			=>	$disc_text,
	];

	if( $pl[ "slow" ] === "Yes" ) {
		$pl[ "slow" ] = TRUE;
	} else {
		$pl[ "slow" ] = FALSE;
	}

	if( $pl[ "died" ] === "Yes" ) {
		$pl[ "died" ] = TRUE;
	} else {
		$pl[ "died" ] = FALSE;
	}
    
    $downtime = new stdClass();
    $downtime->days = (int)$user[ 18 ];
    $downtime->checks = [];

	$res = trim( $user[ 19 ] );
	
	$select = [
		'task_level',
		'dc',
		'failed',
		'trained',
		'expert',
		'master',
		'legendary'
	];

	$where = [
		'task_level' => $user[ 22 ],
	];

	$ei_sql = select_sql( $select, 'downtime', $where );
	
	$where_crit = [
		'task_level' => $user[ 22 ] + 1,
	];

	$ei_crit_sql = select_sql( $select, 'downtime', $where_crit );

	$ei_info = $ei_sql[ 0 ];
	$ei_crit = $ei_crit_sql[ 0 ];

    if( $downtime->days > 8 ) {
        $exp_res = explode( "/",  $res );
		$exp_skill = explode( "/", $user[ 20 ] );
        
        for( $i = 0 ; $i < count( $exp_res ) ; $i++ ) {
            $check = new stdClass();
            $check->skill = trim( $exp_skill[ $i ] );
    
			$check->prof = strtolower( $user[ 21 ] );
			           
            $check->result = (int)substr( $exp_res[ $i ], 0, strlen( $exp_res[ $i ] ) );
			$check->days = min( $downtime->days - ( $i * 8 ), 8 );
			$check->dc = (int)$ei_info[ "dc" ];

			if( $check->result >= $check->dc ) {

				if ( $check->result >= $check->dc + 10 ) {

					$check->level = "CS";
					$check->per_diem = (float)$ei_crit[ $check->prof ];
					$check->total = number_format( $check->per_diem * $check->days, 2, ".", "," );

				} else {

					$check->level = "S";
					$check->per_diem = (float)$ei_info[ $check->prof ];
					$check->total = number_format( $check->per_diem * $check->days, 2, ".", "," );
				}

			} else {

				if ( $check->result <= $check->dc - 10 ) {

					$check->level = "CF";
					$check->per_diem = 0;
					$check->total = number_format( $check->per_diem * $check->days, 2, ".", "," );

				} else {

					$check->level = "F";
					$check->per_diem = (float)$ei_info[ "failed" ];
					$check->total = number_format( $check->per_diem * $check->days, 2, ".", "," );
				}
			}
    
            array_push( $downtime->checks, $check );
        }
    } else {

        $check = new stdClass();
        $check->skill = $user[ 20 ];

		$check->prof = strtolower( $user[ 21 ] );;
		
        $check->result = (int)substr( $res, 0, strlen( $res ) );
		$check->days = $downtime->days;
		
		$prof = substr( trim( $exp_res[ $i ] ), -1 );

		$check->level = $user[ 23 ];
		$check->total = $user[ 24 ];
			 
		$check->dc = (int)$ei_info[ "dc" ];

		array_push( $downtime->checks, $check );
        
	}
	$downtime->notes = $user[ 25 ];

    $pl[ "downtime" ] = $downtime;

    return $pl;
}

function parse_web_submit_user( $user, $obj ) {

	$player = $obj->players;
	$event = $obj->event;

	if( $user === "gm" ) {

		$scr = "gm_char_";

	} else {
		
		$scr = "p_$user" . "_";
	}

	$pl = [
		"name" 			=>	$player[ $scr . "name" ],
		"char"			=> 	$player[ $scr . "char" ],
		"pzo_id"		=>	$player[ $scr . "pzo_id" ],
		"char_no"		=>	$player[ $scr . "char_no" ],
		"faction"		=>	$player[ $scr . "char_faction_sel" ],
		"level"			=>	(int)$player[ $scr . "level" ],
		"purchase"		=>	$player[ $scr . "bought" ],
		"purchase_gp"	=>	number_format( $player[ $scr . "bought_total" ], 2, ".", "," ),
		"sold"			=>	$player[ $scr . "sold" ],
		"sold_gp"		=>	number_format( $player[ $scr . "sold_total" ] / 2, 2, ".", "," ),
		"slow"			=>	$player[ $scr . "slow" ],
		"died"			=>	$player[ $scr . "died" ],
		"file_name"		=>	$player[ $scr . "pzo_id" ] . "-" . $player[ $scr . "name" ] . "-" . $event[ "played" ] . "-" . $event[ "date" ] . ".pdf",
		"add_notes"		=>	$player[ $scr . "notes" ],
	];

	switch ( strtolower( $chronicle[ "pfs_type" ] ) ) {

        case "bounty" :
            $gp = number_format( $event[ "treasure" ], ".", "," );
            if( $pl->slow === "true" ) {
                $gp = $gp / 2;
            }
        break;

        case "quest" :
			$where = [
                'level' => $pl[ "level" ],
			];
			
			$bundle = select_sql( "gp_val", 'treasure_bundles', $where );
			$bundle = $bundle[ 0 ];
			$bundle = (float)$bundle[ "gp_val" ];
            $gp = number_format( $bundle * 2.5, 2, ".", " " );
            if( $pl[ "slow" ] === "true" ) {
                $gp = $gp / 2;
            }
        break;

		case "ap" :
        case "mod" :
			$where = [
                'level' => $pl[ "level" ],
            ];
			$bundle = select_sql( "gp_val", 'treasure_bundles', $where );
			$bundle = $bundle[ 0 ];
			$bundle = (float)$bundle[ "gp_val" ];
            $gp = number_format( $bundle * 30, 2, ".", " " );
            if( $pl[ "slow" ] === "true" ) {
                $gp = $gp / 2;
            }
        break;

        default : 
			$where = [
				'level' => $pl[ "level" ],
			];
			$bundle = select_sql( "gp_val", 'treasure_bundles', $where );
			$bundle = $bundle[ 0 ];
			$bundle = (float)$bundle[ "gp_val" ];

			$gp = $bundle;
			if( $user === "gm" ) {
				$gp = number_format( $bundle * 10, 2, ".", "," );
			} else {
				$gp = number_format( $bundle * $event[ "treasure" ], 2, ".", "," );
			}
			            
            if( $pl[ "slow" ] === "true" ) {
                $gp = $gp / 2;
			}
			
        break;

	}
	
	$pl[ "gp" ] = $gp;

	$downtime = new stdClass();
    $downtime->days = (int)$user[ 18 ];
    $downtime->checks = [];

	return $pl;
}

function send_chronicle_webhook( $obj, $user, $file_loc ) {

	$disc = $obj->disc;
	$webhook = $disc[ "webhook" ];
	$bot_name = $disc[ "bot_name" ];
	$bot_image = $disc[ "avatar" ];

	$msg = [
		"username"		=>	$bot_name,
		"avatar_url"	=>	$bot_image,
		"content"		=>	$user[ "disc" ],
		"tts" 			=> 	"false",
	];
	
	
	$ch = curl_init( $webhook );
	$cfile = new CURLFile( realpath( $file_loc ), "application/pdf", $user[ "file_name" ] );
	$msg[ "file" ] = $cfile;

	curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
	curl_setopt( $ch, CURLOPT_POST, 1);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $msg );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_HEADER, 0);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$return->msg = $msg;
	$result = curl_exec( $ch );

	$return->result = $result;
	curl_close( $ch );

	sleep( 1 );

	return $return;
}

function write_add_notes( $pdf, $obj, $user ) {

	$xy = $obj->chronicle[ "coords" ];

	$pdf->SetFont( $xy[ 'std_font' ], '', $xy[ 'sm_size' ] );
	$pdf->SetXY( $xy[ 'add_notes_x'], $xy[ 'add_notes_y'] );
	$pdf->MultiCell( 72, 3.75, $user[ "add_notes" ], 0, "\n" );
}

function write_downtime_info( $pdf, $obj, $user ) {

	$chronicle = $obj->chronicle;

	$xy = $chronicle[ "xy" ];
	$wh = $chronicle[ "wh" ];

	$std_font = $obj->font[ "std" ];
	$std_size = $obj->font[ "size" ];
	$sm_size = $obj->font[ "sm" ];

	$act = $user[ "downtime" ];

	$total = 0;

	for ( $i = 0 ; $i < count( $act->checks ) ; $i++ ) {

		$check = $act->checks[ $i ];

		if ( isset( $income_dc ) ) {
			$ei_tl_dc .= "|" . $check->dc;
		} else {
			$ei_tl_dc = "Earn Income DC " . $check->dc;
		}

		$roll = $check->result . ucwords( substr( $check->prof, 0, 1 ) ) . ":" . $check->level;

		$check->per_diem = number_format( $check->total / $check->days, 2, ".", "," );

		if( isset( $ei_skill ) ) {
			$ei_skill .= "| [" . $check->skill . "]" . $roll . " " . $check->per_diem . "g for " . $check->days . " days is " . $check->total . "g";
		} else {
			$ei_skill = "[" . $check->skill . "] " . $roll . " " . $check->per_diem . "g for " . $check->days . " days is " . $check->total . "g";
		}	

		$total += $check->total;
	} 

	$total = number_format( $total, 2, ".", "," );

	$ei_msg = $ei_tl_dc . $ei_skill . "\nTotal: $total" . "g\n" . $act->notes; 

	$pdf->SetFont( $std_font, '', $sm_size );

	$h = $wh[ "btm_note_h" ];
	$w = $wh[ "btm_note_w" ];

	$halign = "L";
	$valign = "T";

	$pdf->SetXY( $xy[ 'dt_notes_x' ], $xy[ 'ch_bottom_notes' ] );	
	$pdf->draw_text_box( $ei_msg, $w, $h, $halign, $valign );

	$pdf->SetFont( $std_font, "", $std_size );
	$h = $wh[ "h" ];
	$w = $wh[ "w" ];
	$x = $xy[ "ch_x" ];

	$halign = "R";
	$valign = "M";

	$pdf->SetXY( $xy[ "ch_x" ], $xy[ "dt_income_y" ] );
	$pdf->draw_text_box( "+ $total", $w, $h, $halign, $valign );

	unset( $ei_msg );
	unset( $ei_tl_dc );
	unset( $exp_skill );
}

function write_gained_info( $pdf, $obj, $user ) {

	$chronicle = $obj->chronicle;
	$event = $obj->event;
	$xy = $chronicle[ "xy" ];
	$wh = $chronicle[ "wh" ];

	$faction = $user[ "faction" ];
	$b_faction = $event[ "b_rep_fac" ];

	$std_font = $obj->font[ "std" ];
	$std_size = $obj->font[ "size" ];
	$sm_size = $obj->font[ "sm" ];
	//XP Gained
	$xp = (int)$chronicle[ 'xp' ];

	//GP Gained
	$gp = $user[ 'gp' ];

	//Rep Gained
	$rep = $event[ "rep" ];

	if( strpos( $chronicle[ "tag" ], "Faction" ) !== FALSE ) { 
		$b_rep = $event[ "b_rep" ];
	}

	if( $user[ "slow" ] === 'Yes' ) {
		$xp = $xp / 2;
		$gp = $gp / 2;
		$rep = $rep / 2;
		if( strpos( $chronicle[ "tag" ], "Faction" ) !== FALSE ) {
			$b_rep = $b_rep / 2;
		}
	}
	
	//Write Settings
	$pdf->SetFont( $std_font, '', $std_size );
	$h = $wh[ "h" ];
	$w = $wh[ "w" ];
	$x = $xy[ "ch_x" ];

	$halign = "C";
	$valign = "M";

		//XP
		$pdf->SetXY( $x, $xy[ "er_xp_y" ] );
		$pdf->draw_text_box( "+ $xp", $w, $h, $halign, $valign );
	
		$halign = "R";

		//GP
		$pdf->SetXY( $x, $xy[ "er_gp_y" ] );
		$pdf->draw_text_box( "+ $gp", $w, $h, $halign, $valign );

		//Rep
		if( $wh[ "fb_name_w" ] == "na" ) {

			$msg = "$faction +$rep";
		
			if( strpos( $chronicle[ "tag" ], "Faction" ) !== FALSE ) {

				if( $user[ "slow" ] ) {
					$p_b_rep = $b_rep / 2;
					
				} else {
					$p_b_rep = $b_rep;
				}

				$msg .= " & $b_faction +$p_b_rep" ;
			
			}

			$pdf->SetFont( $std_font, '', $sm_size );

			$halign = "L";
			$valign = "T";

			$pdf->SetXY( $xy[ "fb_name_x" ], $xy[ "fb_slot_y" ] );
			$pdf->draw_text_box( $msg, $wh[ "fb_w"], $wh[ "fb_h" ], $halign, $valign );

		} else {

			$halign = "L";
			$valign = "B";

			$h = $wh[ "fb_h" ];

			$name_x = $xy[ "fb_name_x" ];
			$name_w = $wh[ "fb_name_w"];

			$slot_y = $xy[ "fb_slot_y" ];
			$bon_y = $xy[ "fb_bonus_y" ];
			
			$rep_x = $xy[ "fb_rep_x" ];
			$rep_w = floatval( $wh[ "fb_rep_w" ] );
	
			//Slotted Faction 
			//Name ( The slotted faction is set in Player Info )
			//Rep Gained
			if( $pl[ "slow" ] ) {
				$p_rep = $rep / 2;
			} else {
				$p_rep = $rep;
			}
			$pdf->SetFont( $std_font, '', $sm_size );

			$pdf->SetXY( $rep_x, $slot_y );
			$pdf->draw_text_box( "+ $p_rep", $rep_w, $h, $halign, $valign );

			//Bonus Faction
			//Is it there
			if ( $bonus_rep ) {
				//Name
				$pdf->SetXY( $name_x, $bon_y );
				$pdf->draw_text_box( $b_faction, $name_w, $h, $halign, $valign );

				//Rep Earned
				if( $slow ) {
					$p_b_rep = $b_rep / 2;
					
				} else {
					$p_b_rep = $b_rep;
				}
				$pdf->SetXY( $rep_x, $bon_y );
				$pdf->draw_text_box( "+ $p_b_rep", $rep_w, $h, $halign, $valign );
			}

			//Bonus rep Gained
			if( strpos( $chronicle[ "tag" ], "Faction" ) !== FALSE ) {
				$b_faction = $event[ "b_rep_fac" ];
				$b_total = $event[ "b_rep" ];

				if( $user[ "slow" ] === 'Yes' ) {
					$b_total = $b_total / 2;
				}

				$pdf->SetFont( $std_font, '', $sm_size );
				$pdf->SetXY( $xy[ 'fb_name_x' ], $xy[ 'fb_bonus_y' ] );
				$pdf->draw_text_box( $b_faction, $wh[ "fb_name_w" ], $wh[ "fb_h" ], $halign, $valign );

				$pdf->SetXY( $xy[ 'fb_rep_x' ], $xy[ 'fb_bonus_y' ] );
				$pdf->draw_text_box( "+ $b_total", $wh[ "fb_rep_w" ], $wh[ "fb_h" ], $halign, $valign );
			}

		}
}	

function write_gm_block( $pdf, $obj ) {

	$chronicle = $obj->chronicle;
	$event = $obj->event;
	$xy = $chronicle[ "xy" ];
	$wh = $chronicle[ "wh" ];

	$std_font = $obj->font[ "std" ];
	$sig_font = $obj->font[ "sig" ];
	$std_size = $obj->font[ "size" ];
	$sm_size = $obj->font[ "sm" ];

	$pdf->SetFont( $std_font, '', $std_size );
	$h = $wh[ "et_h" ];
	$y = $xy[ "et_y" ];
			
	$halign = "C";
	$valign = "B";

	//Event Name
	$pdf->SetXY( $xy[ 'et_name_x'], $y );
	$pdf->draw_text_box( $event[ "name" ], $wh[ "et_name_w"], $h, $halign, $valign );

	//Event Code
	$pdf->SetXY( $xy[ 'et_code_x'], $y );
	$pdf->draw_text_box( $event[ "code" ], $wh[ "et_code_w"], $h, $halign, $valign );

	//Event Date
	$pdf->SetXY( $xy[ 'et_date_x'], $y );
	$pdf->draw_text_box( $event[ "date" ], $wh[ "et_date_w"], $h, $halign, $valign );

	//GM ID
	$pdf->SetXY( $xy[ 'et_gm_id_x'], $y );
	$pdf->draw_text_box( $event[ "gm_id" ], $wh[ "et_id_w"], $h, $halign, $valign );

	//GM Signature
	if( $wh[ "et_sig_w" ] == "na" ) {

	} else { 
		$pdf->AddFont( 'Maghrib', 'BI', "maghribbi.php" );
		$pdf->SetFont( $sig_font, 'BI', $std_size + 10 );
		$pdf->SetXY( $xy[ 'et_sig_x'], $y );
		$pdf->CellFit( $wh[ "et_sig_w" ], $h, $event[ "gm" ], $align="C" );
	}
}

function write_player_info( $pdf, $obj, $user ) {

	$chronicle = $obj->chronicle;
	$xy = $chronicle[ "xy" ];
	$wh = $chronicle[ "wh" ];

	$std_font = $obj->font[ "std" ];
	$std_size = $obj->font[ "size" ];
	$sm_size = $obj->font[ "sm" ];

	$pdf->SetFont( $std_font, '', $std_size );
	$y = $xy[ "pb_y" ];
	$h = $wh[ "pb_h"];
	$halign = "L";
	$valign = "B";

	//Player Name
		//Season 2 v 1 chronicles dont have a space for player name
	if( $wh[ "pb_name_w" ] == "na" ) {

	} else {
		//Write Player Name
		$name = $user[ "name" ];
		$pdf->SetXY( $xy[ 'pb_name_x' ], $y );
		$pdf->draw_text_box( $name, $wh[ "pb_name_w" ], $h, $halign, $valign );
	}
					
	//Write Character Name
	$char = $user[ "char" ];
	$pdf->SetXY( $xy[ 'pb_char_x' ], $y );
	$pdf->draw_text_box( $char, $wh[ "pb_char_w"], $h, $halign, $valign );
	
	//Org Play ID
	$id = $user[ "pzo_id" ];
	$pdf->SetXY( $xy[ 'pb_pzo_id_x' ], $y );
	$pdf->draw_text_box( $id, $wh[ "pb_pzo_id_w"], $h, $halign, $valign );

	//Character Number
	$char_no =  " " . substr( $user[ "char_no" ], $wh[ "pb_char_no_substr" ] );
	$pdf->SetXY( $xy[ 'pb_char_no_x' ], $y );
	$pdf->draw_text_box( $char_no, $wh[ "pb_char_no_w"], $h, $halign, $valign );

	//Rep Box
	$faction = $user[ "faction" ];

	//Season 2 v1 Chronicles really changed the Reputation style
	if( $wh[ "fb_name_w" ] == "na" ) {

		$msg = "$faction";
			
		$pdf->SetFont( $std_font, '', $sm_size );

		$halign = "L";
		$valign = "T";

		$pdf->SetXY( $xy[ "fb_name_x" ], $xy[ "fb_slot_y" ] );
		$pdf->draw_text_box( $msg, $wh[ "fb_w"], $wh[ "fb_h" ], $halign, $valign );
	} else {

		$pdf->SetFont( $std_font, '', $sm_size );

		$halign = "L";
		$valign = "B";

		$h = $wh[ "fb_h" ];

		$name_x = $xy[ "fb_name_x" ];
		$name_w = $wh[ "fb_name_w"];

		$slot_y = $xy[ "fb_slot_y" ];
		$bon_y = $xy[ "fb_bonus_y" ];
		
		$rep_x = $xy[ "fb_rep_x" ];
		$rep_w = floatval( $wh[ "fb_rep_w" ] );

		//Name
		$pdf->SetXY( $name_x, $slot_y );
		$pdf->draw_text_box( $faction, $name_w, $h, $halign, $valign );
	}
}

function write_shopping_info( $pdf, $obj, $user ) {

	$xy = $obj->chronicle[ "xy" ];
	$wh = $obj->chronicle[ "wh" ];

	$std_font = $obj->font[ "std" ];
	$std_size = $obj->font[ "size" ];
	$sm_size = $obj->font[ "sm" ];

	$pdf->SetFont( $std_font, '', $std_size );

	$h = $wh[ "sh_note_h" ];
	$w = $wh[ "sh_note_w" ];
	$x = $xy[ "sh_note_x" ];

	$halign = "L";
	$valign = "T";

	//Sold Notes
	$pdf->SetXY( $x, $xy[ "sh_sold_notes_y" ] );
	$pdf->draw_text_box( $sold_notes, $w, $h, $halign, $valign );

	//Buy Notes
	$bought_notes = $user[ "purchase" ];
	$pdf->SetXY( $x, $xy[ "sh_buy_notes_y" ] );
	$pdf->draw_text_box( $bought_notes, $w, $h, $halign, $valign );


	//Notes Total
	$h = $wh[ "sh_note_total_h" ];
	$w = $wh[ "sh_note_total_w" ];
	$x = $xy[ "sh_note_total_x" ];

	$halign = "C";
	$valign = "M";
		//Sold
		if( $user[ "sold_gp" ] === NULL ) {

		} else {
			$sold_gp = number_format( $user[ "sold_gp" ] + 0, 2, ".", "," );
			$sold_gain = number_format( ( $user[ "sold_gp" ] + 0 )/ 2, ".", "," );

			$pdf->SetXY( $x, $xy[ "sh_sold_note_total_y" ] );
			$pdf->draw_text_box( $sold_gp, $w, $h, $halign, $valign );

			$h = $wh[ "h" ];
			$w = $wh[ "w" ];
			$x = $xy[ "ch_x" ];
		
			$halign = "R";
			$valign = "M";
			
			$pdf->SetXY( $x, $xy[ "sh_sold_value_y" ] );
			$pdf->draw_text_box( "+ $sold_gain", $w, $h, $halign, $valign );
		}

		//Buy
		if ( $user[ "purchase_gp" ] === NULL ) {

		} else {
			$bought_gp =  number_format( $user[ "purchase_gp" ], 2, ".", "," );

			$pdf->SetXY( $x, $xy[ "sh_buy_notes_total_y" ] );
			$pdf->draw_text_box( $bought_gp, $w, $h, $halign, $valign );
			
			$h = $wh[ "h" ];
			$w = $wh[ "w" ];
			$x = $xy[ "ch_x" ];
		
			$halign = "R";
			$valign = "M";

			$pdf->SetXY( $x, $xy[ "sh_buy_value_y" ] );
			$pdf->draw_text_box( "- $bought_gp", $w, $h, $halign, $valign );
		}
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

function write_tier_x( $pdf, $obj ) {

	$pdf->SetLineWidth( 3 );

	$event = $obj->event;
	$chronicle = $obj->chronicle;

	if ( isset( $event[ "tier" ] ) ) {
		$tier = $event[ "tier" ];
		$xy = $chronicle[ "coords" ];
		
		switch ( $tier ) {

			case "high" :
				$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_high_top' ], $xy[ 'xo_right' ], $xy[ 'xo_high_bottom' ] );
				$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_high_bottom' ], $xy[ 'xo_right' ], $xy[ 'xo_high_top' ] );
			break;

			case "low" :
				$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_low_top' ], $xy[ 'xo_right' ], $xy[ 'xo_low_bottom' ] );
				$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_low_bottom' ], $xy[ 'xo_right' ], $xy[ 'xo_low_top' ] );
			break;
		}
	}
}

?>