<?php
require 'SendMailClass.php';

$toMailId = "jai.prakash@trinityapplab.co.in";
// $ccMailId = "pushkar.tyagi@trinityapplab.co.in";
$ccMailId = "";
$bccMailId = "";


$msg = "Hi,
this is jai prakash";
$msg = nl2br($msg);
$subject = "Cron test";
$classObj = new SendMailClass();
$response = $classObj->sendMail($toMailId, $ccMailId, $bccMailId, $subject, $msg, null);
?>