<?php

$path = dirname(__FILE__);
// echo $path;
$cron = $path . "/sendMail.php";
echo exec("***** php -q ".$cron." &> /dev/null");

?>