<?php
// header("Access-Control-Allow-Origin: https://www.trinityapplab.in");
// header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Pragma, Cache-Control, Content-Length, Accept-Encoding");
// header('X-Content-Type-Options: nosniff');
// header('Referrer-Policy: no-referrer');
// header('X-XSS-Protection: 1; mode=block');
// header('Content-Security-Policy-Report-Only: policy');
// header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:content-type");
$conn=mysqli_connect("localhost","username","password","db_name");
mysqli_set_charset($conn, 'utf8');
?>