<?php

function haversine($lat1, $lon1, $lat2, $lon2) {
    // Convert latitude and longitude from degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    // Haversine formula
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat / 2) * sin($dlat / 2) +
         cos($lat1) * cos($lat2) *
         sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // echo $a.'--'.$c.'--';
    
    // Radius of the Earth in kilometers
    $R = 6371.0;
    $distance = $R * $c; // Distance in kilometers
    // echo $distance;
    return $distance;
}

function is_within_radius($lat1, $lon1, $lat2, $lon2, $radius_km = 5) {
    return haversine($lat1, $lon1, $lat2, $lon2) <= $radius_km;
}

// Example usage
$my_lat = 28.7967727724605; // Your latitude
$my_lon = 77.12946362793446;  // Your longitude
$other_lat = 28.9445083; // Other user's latitude
$other_lon = 77.1038584; // Other user's longitude

if (is_within_radius($my_lat, $my_lon, $other_lat, $other_lon)) {
    echo "The user is within 5 km radius.";
} else {
    echo "The user is outside the 5 km radius.";
}
?>