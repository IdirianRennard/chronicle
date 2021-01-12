<?php
include 'include.php';

$output = new stdClass ();

$pf2 = new stdClass ();

//PFS2 MIN and MAX table size;
$pf2->min_p_no = 2;
$pf2->max_p_no = 6;

$factions = [
    'Envoys\' Alliance',
    'Grand Archive',
    'Horizon Hunters',
    'Vigilant Seal',
    'Radiant Oath',
    'Verdant Wheel',
];

$activities = [
    'Earn Income',
    'Crafting',
    'Other',
    ' None',
];

$skills = [
    'Acrobatics',
    'Arcana',
    'Athletics',
    'Crafting',
    'Deception',
    'Diplomacy',
    'Intimidation',
    'Medicine',
    'Nature',
    'Occultism',
    'Performance',
    'Religon',
    'Society',
    'Stealth',
    'Survival',
    'Thievery',
    'Lore',
];

$sheets = [
    "Omaha PFS"     =>  "https://docs.google.com/spreadsheets/d/14L2BcQXkfIfh6Gjbw763NC2LrwnqD4jDhUKmj4xNIZM/edit?usp=sharing",
    "AdAstraGames"  =>  "https://docs.google.com/spreadsheets/d/1N7kIJtxzJxJlefnuk8RDjvfITJ12jpqiP4mjR49MNpA/edit?usp=sharing",
];

ksort( $sheets );

$pf2->downtime = $activities;

$pf2->factions = $factions;

$pf2->skills = $skills;

$pf2->sheets = $sheets;

$output->pf2 = $pf2;

json_return( $output );

?>