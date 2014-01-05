#!/usr/bin/php -q
<?php

require_once('PHPOTP.php');
require_once('Base32.php');

$Secret=PHPOTP::GenSeed();
$Base32Secret=Base32::Encode($Secret);
echo 'Secret (Base32): '.$Base32Secret."\n";

$OTP=new PHPOTP();
echo 'HOTP: '.$OTP->HOTP($Secret)."\n";
echo 'TOTP: '.$OTP->TOTP($Secret)."\n";

echo $OTP->HOTPAsURI('The Issuer','A Label',$Base32Secret,0)."\n";
echo $OTP->TOTPAsURI('The Issuer','A Label',$Base32Secret)."\n";
?>
