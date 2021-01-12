<?php
//use setasign\Fpdi\Fpdi;

//include files
include 'include.php';
include './db/db_scr.php';

require_once('fpdf/methos.php');

	//include all inclusion files
	include_fofx();

	//get the data
	$data = $_POST;

	//Game system
	$system = $data[ 'system' ];

	$discord = $data[ "pf2_discord" ];
	$discord_url = $data[ "pf2_discord_link" ];
	
	$g_type = $data[ 'pf2_g_type_sel' ];

	$game_name = substr( $data[ 'game_name' ], 0, strpos( $data["game_name"], "(" ) - 1 );

	$e_code = $data[ 'pf2_gm_e_code' ];
	$e_date = $data[ 'pf2_gm_e_date' ];
	$e_name = $data[ 'pf2_gm_e_name' ];

	$gm_name = $data[ 'pf2_gm_name' ];
	$gm_id = $data[ 'pf2_gm_pzo_id' ];

	$file_loc = "./$system/$g_type/";

	$return = new stdClass();

	$return->discord_test = $discord;

	if( $discord === "true" ) {
		$return->webhook = $discord_url;
	}
	
	$created = [];

	switch ( $g_type ) {

		case 'ap' :
			$set[ 'xp' 		] = 12;

			$btq = substr( $data[ 'pf2_ap_camp_sel' ], 0, strpos( $data[ 'pf2_ap_camp_sel' ], "-" ) );

			$file_loc .= $data[ 'pf2_g_name_sel' ] . "/$btq.pdf";	

		break;

		case 'pfs' :

			switch ( $data[ 'pf2_g_name_sel' ] ) {

				case 'q' :

				$set[ 'xp' 		] = 1;
	
				$btq = substr( $data[ 'pf2_pfs_seas_sel' ], 0, strpos( $data[ 'pf2_pfs_seas_sel' ], "-" ) );

				$file_loc .= strtolower( $data[ 'pf2_g_name_sel' ] ) . "/$btq.pdf";	
				break;

				case 'b' :

				$set[ 'xp' 		] = 1;
	
				$btq = substr( $data[ 'pf2_pfs_seas_sel' ], 0, strpos( $data[ 'pf2_pfs_seas_sel' ], "-" ) );
	
				$file_loc .= strtolower( $data[ 'pf2_g_name_sel' ] ) . "/$btq.pdf";	

				break;

				default :

				$set[ 'xp' 		] = 4;
	
				$btq = substr( $data[ 'pf2_pfs_seas_sel' ], 0, strpos( $data[ 'pf2_pfs_seas_sel' ], "-" ) );

				$file_loc .= strtolower( $data[ 'pf2_g_name_sel' ] ) . "/$btq.pdf";	

				break;


			}		
		break;

		case 'saa' :
			$set[ 'xp' 		] = 12;

			$file_loc .= $data[ 'pf2_g_name_sel' ] . "/" . $data[ 'pf2_pfs_seas_sel' ] . ".pdf";

			$btq = substr( $data[ 'pf2_ap_camp_sel' ], 0, strpos( $data[ 'pf2_ap_camp_sel' ], "-" ) );

		break;
	}

	$where = [ 
		'pzo_btq'	=> $btq
	];

	$xy = select_sql( '*', 'xy', $where );
	$xy = $xy[0];

	$return->xy = $xy;
	
	$where = [
		"format"	=>	$xy[ "ch_format" ],
	];
	
	$wh = select_sql( "*", "wh", $where );
	$wh = $wh[ 0 ];

	//json_return( $wh );

	//get system, game type, and game info
	//call db for coords of each field

	//treasure bundles
	$bundles = select_sql( '*', 'treasure_bundles', NULL );
	$arr = [];

	foreach( $bundles as $k => $v ) {
		$arr[ (int)$v[ 'level' ] ] = (float)$v[ 'gp_val' ]; 
	}

	$bundles = $arr;

	//rep
	$rep = $data[ 'pf2_s_rep' ];

	//bonus rep
	$b_faction = $data[ 'pf2_s_b_rep_menu_sel' ];
	if ( $b_faction === "_none" ) {
		$bonus_rep = false;
	} else {
		$bonus_rep = true;

		$b_faction = str_replace( "_", " ", $b_faction );
		$b_faction = ucwords( $b_faction );

		$b_rep = $data[ "pf2_s_b_rep_sel" ];
	}

	unset( $arr );

	//tasks
	$tl_select = [
		'task_level',
		'dc',
		'failed',
		'trained',
		'expert',
		'master',
		'legendary'
	];

	$grad_suc = [
		'CF',
		'F',
		'S',
		'CS',
	];

	$tasks = select_sql( $tl_select, 'downtime', NULL );
		
	$cert_raw = [];
	
	$player_no = $data[ 'pf2_s_no_players' ];

	$return->player_no = $player_no;

	$players = [];
	$players[0] = new stdClass();

	for ( $i = 0 ; $i <= $player_no ; $i++ ) {

		foreach( $data as $k => $v ) {

			if ( $i === 0 ) {

				if( strpos( $k, "gm_char" ) !== false ) {
	
					$title = substr( $k, strpos(  $k, "gm_char_" ) + strlen( "gm_char_")  );
					$players[ $player_no + 1 ]->$title = $v;
				} else {

					unset( $players[ $i ] );
				}
			}

			if( strpos( $k, "_p_" . $i . "_" ) !== false ) {

				$title = substr( $k, strpos( $k, "_p_" . $i . "_" ) + strlen(  "_p_" . $i . "_" ) );
				$players[ $i ]->$title = $v;

			}
		}
	}

	$return->format = $wh;

	$return->certs = $players;
	$return->downtime = [];

	$game_folder = "$e_code-$game_name-$e_date";
	mkdir( "./zip/$game_folder", 0777, true );

	foreach( $players as $k => $v ) {

		$name = $v->pzo_id . "-" . $v->name . "-$game_name-$e_date.pdf"; 

		$zip_folder = "./zip/$game_folder/";
		$dest = $zip_folder . $name;
		
		$no_credit = $v->no_credit;
		$pregen = $v->pregen;
		$fca = ( $v->fca === 'true' );
		$slow = ( $v->slow === 'true' );
		$died = ( $v->death === 'true' );

		$pdf = new PDF();
				
		$pdf->AddPage();
		
		//json_return( $file_loc );
		$pdf->setSourceFile( $file_loc );
		$template = $pdf->importPage(1);
		$pdf->useTemplate( $template );

		$pdf->SetAutoPageBreak(false,0);
		$pdf->SetMargins( 0, 0, 0 );

		$pdf->Footer();
		//Player Box
		$pdf->SetFont( $std_font, '', $std_size );
		$y = $xy[ "pb_y" ];
		$h = $wh[ "pb_h"];
		$halign = "L";
		$valign = "B";

			//Player Name
			if( $wh[ "pb_name_w"] == "na" ) {

			} else {
				$name = "$v->name";
				$pdf->SetXY( $xy[ "pb_name_x" ], $y );
				$pdf->draw_text_box( $name, $wh[ "pb_name_w"], $h, $halign, $valign );
			}

			//Character Name
			$char = $v->char;
			$pdf->SetXY( $xy[ "pb_char_x" ], $y );
			$pdf->draw_text_box( $char, $wh[ "pb_char_w"], $h, $halign, $valign );

			//Paizo ID
			$id = $v->pzo_id;
			$pdf->SetXY( $xy[ "pb_pzo_id_x" ], $y );
			$pdf->draw_text_box( $id, $wh[ "pb_pzo_id_w"], $h, $halign, $valign );
			
			//Character Number
			$char_no =  " " . substr( $v->char_no, $wh[ "pb_char_no_substr"] );
			$pdf->SetXY( $xy[ 'pb_char_no_x' ], $y );
			$pdf->draw_text_box( $char_no, $wh[ "pb_char_no_w"], $h, $halign, $valign );

		
		
		//Rep Box
		$pdf->SetFont( $std_font, "", $sm_size );

		$faction = $v->char_faction_sel;
		$faction = str_replace( "_", " ", $faction );
		$faction = ucwords( $faction );

			//Rep Earned
			if ( $slow ) {
				$p_rep = $rep / 2;
			} else {
				$p_rep = $rep;
			}

		if( $wh[ "fb_name_w" ] == "na" ) {

			$msg = "$faction +$p_rep";
			
			if( $bonus_rep ) {

				if( $slow ) {
					$p_b_rep = $b_rep / 2;
					
				} else {
					$p_b_rep = $b_rep;
				}

				$msg .= "& $b_faction +$p_b_rep" ;
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
				//Name
				$pdf->SetXY( $name_x, $slot_y );
				$pdf->draw_text_box( $faction, $name_w, $h, $halign, $valign );

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

		}
		//Earned XP/GP
			//XP Gained
			$xp = $set[ 'xp' ];

			//GP Gained
			$level = $v->level;
			if(  $data[ 'pf2_g_name_sel' ] === 'b' ) {
			
				$gp = $data[ 'pf2_s_tb' ];
								
			} else {
				$gp = $bundles[ $level ] * $data[ 'pf2_s_tb' ];

				$gp = number_format( $gp, 2, ".", "," );
			}

			if( $slow ) {
				$xp = $xp / 2;
				$gp = $gp / 2;
			}

			//Write Settings
			$pdf->SetFont( $std_font, "", $std_size );
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
			

			//Downtime
			unset( $other_msg );
			unset( $craft_msg );
			unset( $ei_msg );
			unset( $ei_skill );
			unset( $ei_tl_dc );
			unset( $none_msg );

			$dt_list = [];
			$dt_list[0] = new stdClass();

			$earned_gp = 0;

			foreach( $v as $key => $val ) {

				if( strpos( $key, "activity_sel" ) !== false ) {

					$dt_list[]->$key = $val;

				}
			}

			for ( $i = 0 ; $i < count( $dt_list ) + 1 ; $i++ ) {

				foreach( $v as $key => $val ) {

					if( strpos( $key, "dt_$i" ) !== false ) {
						$title = substr( $key, strpos( "dt_$i" ) + 3 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "craft_$i" ) !== false ) {
						$title = "craft_" . substr( $key, strpos( "craft_$i" ) + 6 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "earn_$i" ) !== false ) {
						$title = "earn_" . substr( $key, strpos( "earn_$i" ) + 5 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "item_$i" ) !== false ) {
						$title = "item_" . substr( $key, strpos( "item_$i" ) + 5 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "lore_$i" ) !== false ) {
						$title = "lore_" . substr( $key, strpos( "lore_$i" ) + 5 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "tl_$i" ) !== false ) {
						$title = substr( $key, strpos( "tl_$i" ) + 3 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}

					if( strpos( $key, "other_$i" ) !== false ) {
						$title = "other_" . substr( $key, strpos( "other_$i" ) + 5 + strlen( $i ) + 1 );
						$dt_list[ $i ]->$title = $val;
					}
				}

				$return->downtime[] = $dt_list;

				$activity = $dt_list[ $i ]->activity_sel;

				switch ( $activity ) {

					case "_none" :
						$none_msg = "No Downtime Activities were performed for " . $dt_list[ $i ]->used . " days\n"; 
					break;

					case "crafting" :
								
						$tl = (int)$dt_list[ $i ]->level;

						$roll = $dt_list[ $i ]->craft_roll;
						$check = $roll;
						$roll .= substr( ucwords( $dt_list[ $i ]->skill_sel ), 0, 1 );

						//get output info
						$dt_info = $tasks[ $tl + 2 ];
						$dt_prog = $tasks[ $v->level ];
						$dt_crit = $tasks[ $v->level + 1 ];

						//get roll result
						if( $v->nat1 === "true ") {
							$nat1 === "true";
						} else {
							$nat1 === false;
						}

						if( $v->nat20 === "true ") {
							$nat20 === "true";
						} else {
							$nat20 === false;
						}
						$dt_res = dt_check( $activity, $check, $dt_info[ "dc" ], $nat1, $nat20 );

						$roll .= ":" . $grad_suc[ $dt_res ];

						if( isset( $craft_msg ) ) {
							$craft_msg .= "Crafting [" . $dt_list[ $i ]->item_name;
						} else {
							$craft_msg = "Crafting [" . $dt_list[ $i ]->item_name;
						}						
								
						if( $dt_list[ $i ]->batch === "true" ) {

							$batch = $dt_list[ $i ]->craft_batch_size;
							$craft_msg .= " x" . $dt_list[ $i ]->craft_batch_size;
						}

						$craft_msg .= "] DC " . $dt_info[ 'dc' ] . " $roll";
								
						$prof = $dt_list[ $i ]->skill_sel;
								
						if( $dt_list[ $i ]->craft_prev_days > 4 ) {
							$craft_days = $dt_list[ $i ]->used;
						} else {
							$craft_days = $dt_list[ $i ]->craft_prev_days + $dt_list[ $i ]->used - 4;
						}

						switch ( $dt_res ) {

							case 3 :
								$gp_rem = $dt_list[ $i ]->craft_rem_gp;
								$prog = $dt_crit[ $prof ];
								$final = max( 0, $gp_rem - ( $prog * $craft_days ) );
								$craft_msg .= "Progress $prog" . "g for $craft_days days (" . $dt_list[ $i ]->used ." total) reducing the remaining purchase cost to " . $final . "g.\n";
							break;

							case 2 :
								$gp_rem = $dt_list[ $i ]->craft_rem_gp;
								$prog = $dt_prog[ $prof ];
								$final = max( 0, $gp_rem - ( $prog * $craft_days ) );
								$craft_msg .=  "Progress $prog" . "g for $craft_days days (" . $dt_list[ $i ]->used ." total) reducing the remaining purchase cost to " . $final . "g.\n";
							break;

							case 1 :
								$craft_msg .= "You fail to complete the item. You can salvage the raw materials you supplied for their full value.\n";
							break;

							case 0 :
								$craft_msg .= "You fail to complete the item. You ruin "  . $dt_list[ $i ]->item_cost * .1 . "g of the raw materials you supplied, but you can salvage the rest.\n";
							break;
						}
					break;	

					case "earn_income" :
						//get task level
						if(  $dt_list[ $i ]->task_lv === "true" ) {
							$tl = (int)$dt_list[ $i ]->change;
						} else {
							$level = $v->level;
							$tl = (int)max( [ $level - 2, 0 ] );
						}						
								
						//get output info
						$ei_info = $tasks[ $tl ];
						$ei_crit = $tasks[ $tl + 1 ];

						if( isset( $ei_tl_dc ) ) {									
							$ei_tl_dc .= "|" . $ei_info[ 'dc' ];
						} else {
							$ei_tl_dc = "Earn Income DC " . $ei_info[ 'dc' ];
						}

						$roll = $dt_list[ $i ]->earn_roll;
						$check = $roll;
						$roll .= substr( ucwords( $dt_list[ $i ]->skill_sel ), 0, 1 );

						//get roll result
						$suc_val  = dt_check( $dt_list[ $i ]->activity_sel, $check, $ei_info[ "dc" ] );
									
						$roll .= ":" . $grad_suc[ $suc_val ];

						if( $dt_list[ $i ]->earn_skill_sel === 'lore' ) {

							$skill_name = ucwords( $dt_list[ $i ]->lore_name ) . " Lore";
						} else {
							$skill_name = ucwords( $dt_list[ $i ]->earn_skill_sel );
						}

						$prof = $dt_list[ $i ]->skill_sel;
						
						switch ( $suc_val ) {

							case 3 :
								$ei_earn = $ei_crit[ $prof ];
							break;

							case 2 :
								$ei_earn = $ei_info[ $prof ];
								
							break;

							case 1 :
								$ei_earn = $ei_info[ 'failed' ];

								if( ( $dt_list[ $i ]->exp_prof === "true" ) && $exp_prof_bonus ) {
									$ei_earn += $ei_info[ 'failed' ];
								}
							break;

							case 0 :
								$ei_earn = 0;
							break;
						}							

						$earned_gp = $earned_gp + ( $ei_earn * $dt_list[ $i ]->used );

						if( isset( $ei_skill ) ) {
							$ei_skill .= "| [$skill_name] $roll $ei_earn".  "g for " . $dt_list[ $i ]->used . " days is " . $ei_earn * $dt_list[ $i ]->used . "g.";
						} else {
							$ei_skill = "[$skill_name] $roll $ei_earn".  "g for " . $dt_list[ $i ]->used . " days is " . $ei_earn * $dt_list[ $i ]->used . "g.";
						}

								
						if ( $i == ( count( $dt_list ) - 1 ) ) {

							$ei_msg = "$ei_tl_dc; $ei_skill\nTotal: $earned_gp" . "g\n";
						}
						break;

						case "other" :
							$other_msg = $dt_list[ $i ]->other_name . ": ";
						break;
					}

				}
					
				$dt_msg = "";
				$dt_msg .= $other_msg;
				$dt_msg .= $craft_msg;
				$dt_msg .= $ei_msg;
				$dt_msg .= $none_msg;
				$dt_msg .= $v->downtime_note;
				
				//Write Downtime Notes
				$pdf->SetFont( $std_font, '', $sm_size );
				$h = $wh[ "btm_note_h" ];
				$w = $wh[ "btm_note_w" ];
				
				$halign = "L";
				$valign = "T";

				$pdf->SetXY( $xy[ "dt_notes_x" ], $xy[ "ch_bottom_notes" ] );
				$pdf->draw_text_box( $dt_msg, $w, $h, $halign, $valign );

				$earned_gp = number_format( $earned_gp, 2, ".", "," );

				//Write Earn Income Cell
				$pdf->SetFont( $std_font, "", $std_size );
				$h = $wh[ "h" ];
				$w = $wh[ "w" ];
				$x = $xy[ "ch_x" ];
	
				$halign = "R";
				$valign = "M";

				$pdf->SetXY( $xy[ "ch_x" ], $xy[ "dt_income_y" ] );
				$pdf->draw_text_box( "+ $earned_gp", $w, $h, $halign, $valign );

			//Shopping Info
				//Notes
				$pdf->SetFont( $std_font, '', $std_size );				
				$h = $wh[ "sh_note_h" ];
				$w = $wh[ "sh_note_w" ];
				$x = $xy[ "sh_note_x" ];

				$halign = "L";
				$valign = "T";

					//Sold
					$sold_notes = $v->sold;

					$pdf->SetXY( $x, $xy[ "sh_sold_notes_y" ] );
					$pdf->draw_text_box( $sold_notes, $w, $h, $halign, $valign );

					//Bought
					$bought_notes = "ITEM PURCHASE: " . $v->bought . "\nREP PURCHASE: " . $v->rep_spent;
					
					$pdf->SetXY( $x, $xy[ "sh_buy_notes_y" ] );
					$pdf->draw_text_box( $bought_notes, $w, $h, $halign, $valign );

				//Notes Total
				$h = $wh[ "sh_note_total_h" ];
				$w = $wh[ "sh_note_total_w" ];
				$x = $xy[ "sh_note_total_x" ];

				$halign = "C";
				$valign = "M";

					//Sold
					$sold_total = number_format( $v->sold_total + 0, 2, ".", "," );
					$pdf->SetXY( $x, $xy[ "sh_sold_note_total_y" ] );
					$pdf->draw_text_box( $sold_total, $w, $h, $halign, $valign );

					//Bought
					$buy_total = number_format( $v->bought_total + 0 , 2, ".", "," );
					$pdf->SetXY( $x, $xy[ "sh_buy_notes_total_y" ] );
					$pdf->draw_text_box( $buy_total, $w, $h, $halign, $valign );

				//Income Total
				$h = $wh[ "h" ];
				$w = $wh[ "w" ];
				$x = $xy[ "ch_x" ];

				$halign = "R";
				$valign = "M";

					//Sold
					$sold_total = number_format( $v->sold_total / 2, 2, ".", "," );
					$pdf->SetXY( $x, $xy[ "sh_sold_value_y" ] );
					$pdf->draw_text_box( "+ $sold_total", $w, $h, $halign, $valign );

					//Bought
					$pdf->SetXY( $x, $xy[ "sh_buy_value_y" ] );
					$pdf->draw_text_box( "- $buy_total", $w, $h, $halign, $valign );

			//Tier
			if( $xy[ "xo_top" ] === 0 ) {

			} else {

				$h_tier = ( $data[ 'pf2_tier_high' ] == "true" );
				$l_tier = ( $data[ 'pf2_tier_low' ] == "true" );
	
				$ln_w = 3;
				$pdf->SetLineWidth( $ln_w );
	
				if( $h_tier ) {
					$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_high_top' ], $xy[ 'xo_right' ], $xy[ 'xo_high_bottom' ] );
					$pdf->Line( $xy[ 'xo_left' ] , $xy[ 'xo_high_bottom' ], $xy[ 'xo_right' ], $xy[ 'xo_high_top' ] );
				}
					
				if( $l_tier ) {
					$pdf->Line( $xy[ 'xo_left' ], $xy[ 'xo_low_top' ], $xy[ 'xo_right' ], $xy[ 'xo_low_bottom' ] );
					$pdf->Line( $xy[ 'xo_left' ], $xy[ 'xo_low_bottom' ], $xy[ 'xo_right' ], $xy[ 'xo_low_top' ] );
				}	
	
				$pdf->SetLineWidth( 0 );
			}


			//Additional Notes
			$notes = $v->notes;

			$h = $wh[ "btm_note_h" ];
			$w = $wh[ "btm_note_w" ];

			$halign = "L";
			$valign = "T";

			$pdf->SetFont( $std_font, '', $sm_size );
			$pdf->SetXY( $xy[ "ad_notes_x" ], $xy[ "ch_bottom_notes" ] );
			$pdf->draw_text_box( $notes, $w, $h, $halign, $valign );

			//Event Block
			$pdf->SetFont( $std_font, '', $std_size );
			$h = $wh[ "et_h" ];
			$y = $xy[ "et_y" ];
					
			$halign = "C";
			$valign = "B";

				//Event Name
				$pdf->SetXY( $xy[ 'et_name_x'], $y );
				$pdf->draw_text_box( $e_name, $wh[ "et_name_w"], $h, $halign, $valign );

				//Event Code
				$pdf->SetXY( $xy[ 'et_code_x'], $y );
				$pdf->draw_text_box( $e_code, $wh[ "et_code_w"], $h, $halign, $valign );

				//Event Date
				$pdf->SetXY( $xy[ 'et_date_x'], $y );
				$pdf->draw_text_box( $e_date, $wh[ "et_date_w"], $h, $halign, $valign );

				//GM ID
				$pdf->SetXY( $xy[ 'et_gm_id_x'], $y );
				$pdf->draw_text_box( $gm_id, $wh[ "et_id_w"], $h, $halign, $valign );

				//GM Signature
				if( $wh[ "et_sig_w" ] == "na" ) {

				} else { 
					$pdf->AddFont( 'Maghrib', 'BI', "maghribbi.php" );
					$pdf->SetFont( $sig_font, 'BI', $std_size + 10 );
					$pdf->SetXY( $xy[ 'et_sig_x'], $y );
					$pdf->CellFit( $wh[ "et_sig_w" ], $h, $gm_name, $align="C" );
				}

	
			//Starting & Ending Info
			if( $v->start_values === "true" ) {

				$pdf->SetFont( $std_font, '', $std_size );
				$h = $wh[ "h" ];
				$w = $wh[ "w" ];
				$x = $xy[ "ch_x" ];
				$y = $xy[ "et_y" ];
						
				$halign = "C";
				$valign = "M";

				//Chronicle
				$chron_no = $v->chron_no + 1;

				$pdf->SetXY( $x, $xy[ 'st_chron_no_y' ] );
				$pdf->draw_text_box( $chron_no, $w, $wh[ "st_chron_h" ], $halign, $valign );

				//XP
				$st_xp = $v->start_xp;

				if( $xy[ "ch_format" ] == "seas1_v1.04" ) {

				} else {

					$pdf->SetXY( $x, $xy[ 'st_xp_y' ] );
					$pdf->draw_text_box( $st_xp, $w, $h, $halign, $valign );

				}

				$nd_xp = $st_xp + $xp;

				$pdf->SetXY( $x, $xy[ 'nd_xp_y' ] );
				$pdf->draw_text_box( $nd_xp, $w, $h, $halign, $valign );

				//GP
				$halign = "R";

				$st_gp = $v->start_gp;
					
				$st_gp = number_format( $st_gp, 2, ".", "," );
				$pdf->SetXY( $x, $xy[ 'st_gp_y' ] );
				$pdf->draw_text_box( $st_gp, $w, $h, $halign, $valign );

				$nd_gp = $st_gp + $gp + $earned_gp + $sold_total - $buy_total;

				$nd_gp = number_format( $nd_gp, 2, ".", "," );
				$pdf->SetXY( $x, $xy[ 'nd_gp_y' ] );
				$pdf->draw_text_box( $nd_gp, $w, $h, $halign, $valign );

				//Rep
				$st_rep = $v->start_fame;
				$nd_rep = $st_rep + $p_rep - $v->spent_rep_total;

				if( $wh[ "fb_name_w" ] === "na" ) {					
			
					if( $bonus_rep && $faction === $b_faction ) {
	
						$nd_rep += $p_b_rep;
					}
						
					$msg = "$faction total = $nd_rep";

					$name_x = $xy[ "fb_name_x" ];
					$slot_y = $xy[ "fb_slot_y" ];
			
					$pdf->SetFont( $std_font, '', $sm_size );

					$halign = "R";
					$valign = "B";
			
					$pdf->SetXY( $xy[ "fb_name_x" ], $xy[ "fb_slot_y" ] );
					$pdf->draw_text_box( $msg, $wh[ "fb_w"], $wh[ "fb_h" ], $halign, $valign );

				} else {
					$h = $wh[ "fb_h" ];
					$halign = "C";
					$valign = "B";
										
					$pdf->SetFont( $std_font, "", $sm_size );
	
					$pdf->SetXY( $xy[ "nd_rep_x" ], $slot_y );
					$pdf->draw_text_box( $nd_rep, $rep_w, $h, $halign, $valign );
	
					if( $bonus_rep && $faction === $b_faction ) {
	
						$nd_rep += $p_b_rep;
	
						$pdf->SetXY( $xy[ "nd_rep_x" ], $bon_y );
						$pdf->draw_text_box( $nd_rep, $rep_w, $h, $halign, $valign );
					}
				}
			}
				
			$pdf->Output( "F", $dest );
			

			$created[] = $name;

			if( $discord === "true" ) {

				$pass = new stdClass();

				$disc = [
					"webhook"	=>	$discord_url,
					"bot_name"	=>	"Chronicler",
					"avatar"	=>	"https://pathfinderwiki.com/mediawiki/images/thumb/3/3c/Pathfinder_Society_symbol_2019.jpg/250px-Pathfinder_Society_symbol_2019.jpg",
				];

				$user = [
					"file_name"	=>	$v->pzo_id . "-" . $v->name . "-$game_name-$e_date.pdf",
					"disc"		=>	"Chronicle for " . $v->char,
				];

				$pass->disc = $disc;

				$send = new stdClass();
				$file = $dest;
				$send->file_url = $file;
				$send->status = send_chronicle_webhook( $pass, $user, $dest );
				$return->disc[] = $send;
				$return->result = "DISCORD";
		
			}
		
	}
	$return->created = $created;	
	
	//If this works, provide download link

	if( $discord === "true" ) {

	} else {

		if( zip_certs( $game_folder ) ) {

			$return->result = "SUCCESS";
			$return->ftp = "https://houserennard.online/certs/zip/$game_folder.zip";
	
		}
		
	}

	json_return( $return );
?>