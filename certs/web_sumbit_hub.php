<?php
use setasign\Fpdi\Fpdi;

require_once('fpdf/fpdf.php'); 
require_once( './fpdf/wordwrap.php' );
require_once('fpdi/src/autoload.php'); 

//include files
include 'include.php';
include './db/db_scr.php';


	//include all inclusion files
	include_fofx();

	//get the data
	$data = $_POST;

	call( "./api/log.php", $data );

	//Game system
	$system = $data[ 'system' ];
	unset( $data[ "system" ] );
    
    switch( $system ) {

		case "pf2" :
			$submit = [];
            foreach( $data as $k => $v ) {
				if( strpos( $k,  "pf2_" ) !== FALSE ) {

					$replace = str_replace( "pf2_", "", $k );
					$submit[ $replace ] = $v;

				} else {
					$submit[ $k ] = $v;
				}
			}

			$call = call( $submit, "https://houserennard.online/certs/api/pf2.php" );
			echo $call;
        break;
    }
    /*$played = $data[ "game_name" ];

	$discord = $data[ "pf2_discord" ];
	$discord_url = $data[ "pf2_discord_link" ];
	
	$g_type = $data[ 'pf2_g_type_sel' ];

	$game_name = substr( $data[ 'pf2_game_name' ], 1 );

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

	$xy = select_sql( '*', "xy", $where );
	$xy = $xy[0];

	//get system, game type, and game info
	//call db for coords of each field

	//treasure bundles
	$bundles = select_sql( '*', 'treasure_bundles', NULL );
	$arr = [];

	foreach( $bundles as $k => $v ) {
		$arr[ (int)$v[ 'level' ] ] = (float)$v[ 'gp_val' ]; 
	}

	$bundles = $arr;
	unset( $arr );
		
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
		$fca = $v->fca;
		$slow = $v->slow;
		$died = $v->death;

		$char_name = $v->char;

		$pdf = new FPDI ();
				
		$pdf->AddPage();
		
		//json_return( $file_loc );
		$pdf->setSourceFile( $file_loc );
		$template = $pdf->importPage(1);
		$pdf->useTemplate( $template );

		$pdf->SetAutoPageBreak(false,0);
		$pdf->SetMargins( 0, 0, 0 );

		//Player Info
			//Player Name
			$pdf->SetFont( $std_font, '', $std_size );
			$pdf->SetXY( $xy[ 'name_x' ], $xy[ 'name_y' ] );
			//$wrap_name = wordwrap( $v->name, 21, "\n" );
			$wrap_name = $v->name;
			$pdf->Write( 0, $wrap_name );
				
			//Character Name
			$pdf->SetXY( $xy[ 'char_x' ], $xy[ 'char_y' ] );
			$pdf->SetFont( $std_font, '', $std_size );
			//$wrap_char = wordwrap( $v->char, 21, "\n");
			$wrap_char = $v->char;
			$pdf->Write( 0, $wrap_char );

			//Org Play ID
			$pdf->SetXY( $xy[ 'pzo_id_x' ], $xy[ 'pzo_id_y' ] );
			$pdf->SetFont( $std_font, '', $std_size );
			$pdf->Write( 0, $v->pzo_id );

			//Char Number
			$char_no = $v->char_no;
			$scen_cut = (int)substr( $data[ 'pf2_pfs_seas_sel' ], ( strpos( $data[ 'pf2_pfs_seas_sel' ], "-" ) + 1 ) );

			if ( ($data['pf2_g_type_sel'] === 'pfs') && ($data[ 'pf2_g_name_sel'] == 1) && ( $scen_cut >= 21 ) ) {

				
			} else {

				$char_no = substr( $char_no, 1 );
			}
			

			$pdf->SetXY( $xy[ 'char_no_x' ], $xy[ 'char_no_y' ] );
			$pdf->SetFont( $std_font, '', $std_size );
			$pdf->Write( 0, $char_no );

			//Char Faction
			$faction = $v->char_faction_sel;
			$faction = str_replace( "_", " ", $faction );
			$faction = ucwords( $faction );

			$pdf->SetFont( $std_font, '', $sm_size );
			$pdf->SetXY( $xy[ 'char_faction_x' ], $xy[ 'char_faction_y' ] );
			$pdf->Write( 0, $faction );

			//Starting Info
				//Chronicle
				if ( $v->chron_no === "" || !isset( $v->chron_no )) {

				} else {

					$chron_no = $v->chron_no + 1;

					$pdf->SetFont( $std_font, '', $sm_size );
					$pdf->SetXY( $xy[ 'chron_no_x' ], $xy[ 'chron_no_y' ] );
					$pdf->Write( 0, $chron_no );
				}


				//Starting XP
				if( $v->start_xp === "" ) {

				} else {

					$st_xp = $v->start_xp;
					
					$pdf->SetXY( $xy[ 'st_xp_x' ], $xy[ 'st_xp_y' ] );
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->Write( 0, $st_xp );

				}
				//Starting GP
				if ( $v->start_gp === "" ) {

				} else {

					$st_gp = $v->start_gp;
					
					$pdf->SetXY( $xy[ 'st_gp_x' ], $xy[ 'st_gp_y' ] );
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->Write( 0, $st_gp );
				}


			//Scenario Info		
				//XP Gained
				$xp = $set[ 'xp' ];

				if( $v->slow === 'true' ) {
					$xp = $xp / 2;
				}

				$pdf->SetXY( $xy[ 'er_xp_x' ], $xy[ 'er_xp_y' ] );
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->Write( 0, $xp );

				if ( $v->start_xp === "" || !isset( $v->start_xp ) ) {

				} else {

					$end_xp = $st_xp + $xp;

					$pdf->SetXY( $xy[ 'end_xp_x' ], $xy[ 'end_xp_y' ] );
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->Write( 0, $end_xp );

				}

				//GP Gained
				$level = $v->level;
				if(  $data[ 'pf2_g_name_sel' ] === 'b' ) {

					$gp = $data[ 'pf2_s_tb' ];
					
				} else {
					$gp = $bundles[ $level ] * $data[ 'pf2_s_tb' ];
				}
				

				$pdf->SetXY( $xy[ 'er_gp_x' ], $xy[ 'er_gp_y' ] );
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->Write( 0, $gp );

				//Bonus Fame Gained
				$b_faction = $data[ 'pf2_s_b_rep_menu_sel' ];
					if ( $b_faction === "_none" ) {


					} else {

						$b_faction = str_replace( "_", " ", $b_faction );
						$b_faction = ucwords( $b_faction );
		
						$b_total = $data[ 'pf2_s_b_rep_sel' ];
		
						$pdf->SetFont( $std_font, '', $sm_size );
						$pdf->SetXY( $xy[ 'er_b_fame_faction_x' ], $xy[ 'er_b_fame_faction_y' ] );
						$pdf->Write( 0, $b_faction );
		
						$pdf->SetXY( $xy[ 'er_b_fame_x' ], $xy[ 'er_b_fame_y' ] );
						$pdf->SetFont( $std_font, '', $sm_size );
						$pdf->Write( 0, "+$b_total" );

					}


				//rep Gained
				$rep = $data[ 'pf2_s_rep' ];
				

					if ( $slow === 'true' ) {
						$rep = $rep / 2;
					}
										
					$pdf->SetXY( $xy[ 'er_fame_sm_x' ], $xy[ 'er_fame_sm_y' ] );
					$pdf->SetFont( $std_font, '', $sm_size );
					$pdf->Write( 0, "+$rep" );
					
					if( $v->start_fame === "" || !isset( $v->start_fame ) ) {

					} else {

						$st_rep = $v->start_fame;
						$spent_rep = $v->spent_rep_total;
						
						$end_fame = $st_rep + $rep - $spent_rep;
				
						$pdf->SetXY( $xy[ 'end_fame_sm_x' ], $xy[ 'end_fame_sm_y' ] );
						$pdf->SetFont( $std_font, '', $sm_size );
						$pdf->Write( 0, $end_fame );
	
						if( $b_faction === $faction ) {
	
							$end_fame = $end_fame + $data [ 'pf2_s_b_rep_sel' ];
	
							$pdf->SetXY( $xy[ 'end_fame_w_bonus_x' ], $xy[ 'end_fame_w_bonus_y' ] );
							$pdf->SetFont( $std_font, '', $sm_size );
							$pdf->Write( 0, $end_fame );
						}


					}

				//Tier
				$h_tier = $data[ 'pf2_tier_high' ];
				$l_tier = $data[ 'pf2_tier_low' ];

				$w = 3;
				$pdf->SetLineWidth( $w );

				if( $h_tier === "true" ) {

					$pdf->Line( $xy[ 'high_tier_ul_x' ] , $xy[ 'high_tier_ul_y' ], $xy[ 'high_tier_lr_x' ], $xy[ 'high_tier_lr_y' ] );
					$pdf->Line( $xy[ 'high_tier_ll_x' ] , $xy[ 'high_tier_ll_y' ], $xy[ 'high_tier_ur_x' ], $xy[ 'high_tier_ur_y' ] );
				} 
				
				if( $l_tier === "true" ) {

					$pdf->Line( $xy[ 'low_tier_ul_x' ] , $xy[ 'low_tier_ul_y' ], $xy[ 'low_tier_lr_x' ], $xy[ 'low_tier_lr_y' ] );
					$pdf->Line( $xy[ 'low_tier_ll_x' ] , $xy[ 'low_tier_ll_y' ], $xy[ 'low_tier_ur_x' ], $xy[ 'low_tier_ur_y' ] );
					
				}
										
				//Ending Info			
					//Downtime
					$dt_list = [];
					$dt_list[0] = new stdClass();

					foreach( $v as $key => $val ) {

						if( strpos( $key, "activity_sel" ) !== false ) {

							$dt_list[]->$key = $val;

						}
					}

					$earned_gp = 0;

					unset( $dt_msg );
					unset( $other_msg );
					unset( $craft_msg );
					unset( $ei_msg );
					unset( $ei_skill );
					unset( $ei_tl_dc );
					unset( $none_msg );

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
						switch ( $dt_list[ $i ]->activity_sel ) {

							case "_none" :
								$none_msg = "No Downtime Activities were performed for " . $dt_list[ $i ]->used . " days\n"; 
							break;

							case "crafting" :
								
								$tl = (int)$dt_list[ $i ]->level;

								$roll = $dt_list[ $i ]->craft_roll;
								$check = $roll;
								$roll .= substr( ucwords( $dt_list[ $i ]->skill_sel ), 0, 1 );

								$prog_lv = $v->level;
								$crit_lv = $prog_lv + 1;

								//get output info
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
									'task_level' => $tl + 2,
								];

								$ei_sql = select_sql( $select, 'downtime', $where );

								$where_prog = [
									'task_level' => $prog_lv,
								];

								$prog_sql = select_sql( $select, 'downtime', $where_prog );
								
								$where_crit = [
									'task_level' => $crit_lv,
								];

								$ei_crit_sql = select_sql( $select, 'downtime', $where_crit );
								
								$dt_info = $ei_sql[ 0 ];
								$dt_prog = $prog_sql[ 0 ];
								$dt_crit = $ei_crit_sql[ 0 ];

								$grad_suc = [
									'CF',
									'F',
									'S',
									'CS',
								];

								//get roll result
								switch (true) {

									case ( $check < $dt_info[ 'dc' ] ) :
										if ( $check < ( $dt_info[ 'dc' ] - 10 ) ) {
										//CRITICAL FAILURE!
										$suc_val = 0;
										} else {
										//FAILURE!
										$suc_val = 1;
										}
									break;
									
									case ( $check >= $dt_info[ 'dc' ] ) :
										if ( $check >= ( $dt_info[ 'dc' ] + 10 ) ){
											//CRITICAL SUCCESS!
											$suc_val = 3;
										} else {
											//SUCCESS!
											$suc_val = 2;
										}
									break;
								}

								if( $dt_list[ $i ]->nat_1 === "true" ) {
									$suc_val = max( 0, ( $suc_val - 1 ) );
									$roll .= "(Nat1)";
								}

								if( $dt_list[ $i ]->nat_20 === "true" ) {
									$suc_val = min( 3, ( $suc_val + 1 ) );
									$roll .= "(Nat20)";
								}

								$grad_suc = [
									'CF',
									'F',
									'S',
									'CS',
								];

								$roll .= ":" . $grad_suc[ $suc_val ];

								if( isset( $craft_msg ) ) {
									$craft_msg .= "Crafting [" . $dt_list[ $i ]->item_name;
								} else {
									$craft_msg = "Crafting [" . $dt_list[ $i ]->item_name;
								}						
								
								if( $dt_list[ $i ]->batch === "true" ) {

									$batch = $dt_list[ $i ]->craft_batch_size;
									$craft_msg .= " x" . $dt_list[ $i ]->craft_batch_size;
								}

								$craft_msg .= "] DC " . $dt_info[ 'dc' ] . " $roll\n";
								
								$prof = $dt_list[ $i ]->skill_sel;
								
								if( $dt_list[ $i ]->craft_prev_days > 4 ) {
									$craft_days = $dt_list[ $i ]->used;
								} else {
									$craft_days = $dt_list[ $i ]->craft_prev_days + $dt_list[ $i ]->used - 4;
								}

								switch ( $suc_val ) {

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
									'task_level' => $tl,
								];

								$ei_sql = select_sql( $select, 'downtime', $where );
								
								$where_crit = [
									'task_level' => $tl + 1,
								];

								$ei_crit_sql = select_sql( $select, 'downtime', $where_crit );
								
								$ei_info = $ei_sql[ 0 ];
								$ei_crit = $ei_crit_sql[ 0 ];

								if( isset( $ei_tl_dc ) ) {									
									$ei_tl_dc .= "|" . $ei_info[ 'dc' ];
								} else {
									$ei_tl_dc = "Earn Income DC " . $ei_info[ 'dc' ];
								}

								$grad_suc = [
									'CF',
									'F',
									'S',
									'CS',
								];

								$roll = $dt_list[ $i ]->earn_roll;
								$check = $roll;
								$roll .= substr( ucwords( $dt_list[ $i ]->skill_sel ), 0, 1 );

								//get roll result
								switch (true) {

									case ( $check < $ei_info[ 'dc' ] ) :
	
										if ( $check < ( $ei_info[ 'dc' ] - 10 ) ) {
	
											//CRITICAL FAILURE!
											$suc_val = 0;
	
										} else {
											
											//FAILURE!
											$suc_val = 1;
										}
	
									break;
	
									case ( $check >= $ei_info[ 'dc' ] ) :
										
										if ( $check >= ( $ei_info[ 'dc' ] + 10 ) ){
											
											//CRITICAL SUCCESS!
											$suc_val = 3;
	
										} else {
	
											//SUCCESS!
											$suc_val = 2;
										}
	
									break;
								}


								if( $dt_list[ $i ]->nat_1 === "true" ) {
									$suc_val = max( 0, ( $suc_val - 1 ) );
									$roll .= "(Nat1)";
								}

								if( $dt_list[ $i ]->nat_20 === "true" ) {
									$suc_val = min( 3, ( $suc_val + 1 ) );
									$roll .= "(Nat20)";
								}

								if( $dt_list[ $i ]->exp_prof === "true" ) {
									$exp_prof_bonus = true;
								}

								if( ( $dt_list[ $i ]->exp_prof === "true" ) && $suc_val == 0 ) {
									$exp_prof_bonus = false;
									$suc_val += 1;
								};

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
					
					$pdf->SetFont( $std_font, '', $sm_size );
					$pdf->SetXY( $xy[ 'dt_notes_x'], $xy[ 'dt_notes_y'] );
					$pdf->MultiCell( 72, 3.75, $dt_msg, 0, "\n" );

					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->SetXY( $xy[ 'er_income_x' ], $xy[ 'er_income_y' ] );
					$pdf->Write( 0, $earned_gp );

					//Sold
					$sold_notes = wordwrap( $v->sold, 55, "\n" );

					$pdf->SetFont( $std_font, '', $sm_size );
					$pdf->SetXY( $xy[ 'sold_notes_x'], $xy[ 'sold_notes_y'] );
					$pdf->MultiCell( 88, 4.25, $sold_notes, 0, "\n" );

					$sold_gp = $v->sold_total;

					if ( $sold_gp === '' ) {

						$sold_gp = 0;
					}
					
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->SetXY( $xy[ 'sold_gp_x'], $xy[ 'sold_gp_y' ] );
					$pdf->Write( 0, $sold_gp );

					$sold_gain = $sold_gp / 2;

					$pdf->SetXY( $xy[ 'sold_gain_x'], $xy[ 'sold_gain_y' ] );
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->Write( 0, $sold_gain );

					//Purchase
					$bought_notes = "ITEM PURCHASE: " . $v->bought . "\nREP PURCHASE: " . $v->rep_spent;

					$pdf->SetFont( $std_font, '', $sm_size );
					$pdf->SetXY( $xy[ 'bought_notes_x'], $xy[ 'bought_notes_y'] );
					$pdf->MultiCell( 88, 4.25, $bought_notes, 0, "\n" );

					$bought_gp = $v->bought_total;

					if ( $bought_gp === '' ) {

						$bought_gp = 0;
					}
					
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->SetXY( $xy[ 'bought_gp_x'], $xy[ 'bought_gp_y'] );
					$pdf->Write( 0, $bought_gp );

					$pdf->SetXY( $xy[ 'bought_gp_spent_x'], $xy[ 'bought_gp_spent_y'] );
					$pdf->SetFont( $std_font, '', $std_size );
					$pdf->Write( 0, $bought_gp );
					
					//End GP
					if ( $v->start_gp === "" || !isset( $v->start_gp )) {

					} else {

						$end_gp = $v->start_gp + $gp + $earned_gp + $sold_gain - $bought_gp;

						$pdf->SetXY( $xy[ 'end_gp_x'], $xy[ 'end_gp_y'] );
						$pdf->SetFont( $std_font, '', $std_size );
						$pdf->Write( 0, $end_gp );

					}

				//Additional Notes
				$notes = wordwrap( $v->notes, 49, "\n" );

				$pdf->SetFont( $std_font, '', $sm_size );
				$pdf->SetXY( $xy[ 'add_notes_x'], $xy[ 'add_notes_y'] );
				$pdf->MultiCell( 72, 3.75, $notes, 0, "\n" );

				//GM Block
				//Event Name
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->SetXY( $xy[ 'evt_name_x'], $xy[ 'evt_name_y'] );
				$pdf->Write( 0, $e_name );

				//Event Code
				$pdf->SetXY( $xy[ 'evt_code_x'], $xy[ 'evt_code_y'] );
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->Write( 0, $e_code );

				//Event Date
				$pdf->SetXY( $xy[ 'evt_date_x'], $xy[ 'evt_date_y'] );
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->Write( 0, $e_date );

				//GM Signature
				$pdf->AddFont( 'Maghrib', 'BI', "maghribbi.php" );
				$pdf->SetFont( $sig_font, 'BI', $sig_size );
				$pdf->SetXY( $xy[ 'gm_sig_x'], $xy[ 'gm_sig_y'] );
				$pdf->Write( 0, $gm_name );

				//GM ID
				$pdf->SetFont( $std_font, '', $std_size );
				$pdf->SetXY( $xy[ 'gm_id_x'], $xy[ 'gm_id_y'] );
				$pdf->Write( 0, $gm_id );

				//Created by...
				$pdf->SetFont( $std_font, 'I', $sm_size );
				$pdf->SetXY( $xy[ 'brand_x'], $xy[ 'brand_y'] );
				$pdf->Write( 0, 'Chronicle created by House Rennard' );

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

	json_return( $return );*/
?>