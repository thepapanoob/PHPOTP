#!/usr/bin/php -q
<?php
require_once('PHPOTP.php');
require_once('Base32.php');

function DumpBinStr($BinStr) {
	$Result='';
	$Len=strlen($BinStr);
	for ($i=0; $i<$Len; $i++) {
		$Value=ord($BinStr[$i]);
		if ($Value<16)
			$Result.='0';
		$Result.=dechex($Value).' ';
	}
	return rtrim($Result,' ');
}

/* Straight from RFC 4648 Test Vectors */
$Base32KnownGood=array(
	''=>'',
	'f'=>'MY======',
	'fo'=>'MZXQ====',
	'foo'=>'MZXW6===',
	'foob'=>'MZXW6YQ=',
	'fooba'=>'MZXW6YTB',
	'foobar'=>'MZXW6YTBOI======'
);

echo "\nBase32::Encode Test Vectors\n";
foreach ($Base32KnownGood as $Key=>$Value) {
	echo "'".$Key."' -> '".$Value."': ";
	$Result=Base32::Encode($Key);
	if ($Result===$Value) {
		echo 'PASS';
	} else {
		echo "FAIL ('".$Result."')";
	}
	echo "\n";
}

echo "\nBase32::Decode Test Vectors\n";
foreach ($Base32KnownGood as $Key=>$Value) {
	echo "'".$Value."' -> '".$Key."': ";
	$Result=Base32::Decode($Value);
	if ($Result===$Key) {
		echo 'PASS';
	} else {
		echo "FAIL ('".$Key."')";
	}
	echo "\n";
}

echo "\nBase32 Encode/Decode Excercise";
$Passes=100;
$MaxLen=100;
$Failures=0;
mt_srand();
for ($i=0; $i<$Passes; $i++) {
	$RandStr='';
	$Len=mt_rand(0,$MaxLen);
	for ($Leni=0; $Leni<$Len; $Leni++) {
		$RandStr.=chr(mt_rand(0,255));
	}
	$Encoded=Base32::Encode($RandStr);
	$Decoded=Base32::Decode($Encoded);

	if ($Decoded!=$RandStr) {
		echo "\nFAIL\n";
		echo "Input:\t\t ".DumpBinStr($RandStr)."\n";
		echo "Encoded:\t ".$Encoded."\n";
		echo "Decoded:\t ".DumpBinStr($Decoded)."\n";
		$Failures++;
	}
}
if ($Failures==0)
	echo ": PASS\n";


