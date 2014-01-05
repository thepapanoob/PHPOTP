<?php

/*
 * PHPOTP - An implementation of RFC 4226 (HOTP) and RFC 6238 (TOTP)
 * as a PHP class.
 * Copyright (C) 2014 David Ludlow
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * dave@adsllc.com Toledo, OH
 */

class PHPOTP {
	/* See RFC 4226 and RFC 6238 */
	protected $Algo='sha1';
	protected $Digits=6;
	protected $Interval=30;	/* Seconds */

	public function __construct() {
	}

	public function SetDigits($Value) {	$this->Digits=$Value;	}
	public function SetInterval($Value) {	$this->Interval=$Value;	}

	public function GetDigits() {	return $this->Digits; 	}
	public function GetInterval() {	return $this->Interval; }
	public function GetAlgo() {	return $this->Algo; 	}

	/* Input: $Seed is a binary string
	 * Input: $Count is an increasing 32-bit counter
	 * Return: The resulting OTP value represented as a string with leading zeros.
	 */
	public function HOTP($Seed, $Count=0) {
		/* See RFC 4226 */
		$BinCount=str_pad(pack('N', $Count), 8, chr(0), STR_PAD_LEFT);
		$Hash=hash_hmac($this->Algo, $BinCount, $Seed, true);

		$Offset=ord($Hash[19]) & 0xf;
		$OTPVal=0;
		for ($i=0; $i<4; $i++)
			$OTPVal|=ord($Hash[$Offset+$i])<<(8*(3-$i));
		$OTPVal&=0x7FFFFFFF;
		$OTPVal%=pow(10, $this->Digits);
		return str_pad(strval($OTPVal), $this->Digits, '0', STR_PAD_LEFT);
	}

	/* Input: $Seed is a binary string
	 * Return: The resulting OTP value represented as a string with leading zeros.
	 */
	public function TOTP($Seed,$Time=null) {
		/* See RFC 6238 */
		if ($Time===null)
			$Time=time();
		$TimeSlice=floor($Time/$this->Interval);
		return $this->HOTP($Seed,$TimeSlice);
	}

	public function HOTPAsURI($Issuer,$Label,$Base32Seed,$Count=0) {
		return
			'otpauth://hotp/'.rawurlencode($Issuer).':'.rawurlencode($Label).
			'?secret='.$Base32Seed.
			'&algorithm='.$this->Algo.
			'&digits='.$this->Digits.
			'&count='.$Count.
			'&issuer='.rawurlencode($Issuer);
	}

	public function TOTPAsURI($Issuer,$Label,$Base32Seed) {
		return
			'otpauth://totp/'.rawurlencode($Issuer).':'.rawurlencode($Label).
			'?secret='.$Base32Seed.
			'&algorithm='.$this->Algo.
			'&digits='.$this->Digits.
			'&period='.$this->Interval.
			'&issuer='.rawurlencode($Issuer);
	}

	public function SetAlgo($Value) {
		if (array_search($Value,hash_algos())===FALSE)
			return false;
		$this->Algo=$Value;
		return true;
	}

	public static function GenSeed($Length=20) {
		$Seed='';
		mt_srand();
		for ($i=0; $i<$Length; $i++) {
			$Seed.=chr(mt_rand(0,255));
		}
		return $Seed;
	}
}
?>
