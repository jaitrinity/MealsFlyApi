<?php

$path = dirname(__FILE__);
// echo $path;
$cron = $path . "/sendMailDemo.php";
echo exec("***** php -q ".$cron." &> /dev/null");

?>