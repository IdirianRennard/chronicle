<?php
include 'include.php';

/*#PF1
$pf1 = new tab();
$pf1->name = "Pathfinder";
$pf1->short = "pf1";
$return[] = $pf1;*/

#PF2
$pf2= new tab();
$pf2->name = "PF Second Edition";
$pf2->short = "pf2";
$return[] = $pf2;

#PF2
$upl= new tab();
$upl->name = "Link Sign In Sheet";
$upl->short = "upload";
$return[] = $upl;

#SF
/*$sf= new tab();
$sf->name = "Starfinder";
$sf->short = "sf";
$return[] = $sf;/*/

#echo out the object
echo make_json( $return );

?>