<?php
require_once 'DistanceCalculateClass.php';
$classObj = new DistanceCalculateClass();
$origin="28.6229897,77.3663771";
$destinations="28.6304125415719,77.43533289351471";
$distance = $classObj->calculateDistance($origin, $destinations);
echo $distance;

?>