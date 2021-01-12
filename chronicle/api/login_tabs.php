<?php
include 'include.php';

$tabs = [];

$char = new tab();
$char->name = "Admin";
$char->short = "adm";
$tabs[] = $char;

$char = new tab();
$char->name = "Characters";
$char->short = 'char';
$tabs[] = $char;

/*$char = new tab();
$char->name = "Upload";
$char->short = 'upload';
$tabs[] = $char;*/

$char = new tab();
$char->name = "Profile";
$char->short = 'prof';
$tabs[] = $char;

#echo out the object
json_return( $tabs );

?>