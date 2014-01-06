<?php

/*
 * Base32 - A limited implementation of RFC 4648 (Base32 Encoding) as a PHP class.
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

class Base32 {
	const ASCII_A=65;
	const ASCII_Z=90;
	const ASCII_2=50;
	const ASCII_7=55;
	const PAD='=';

	/* In short, Base32 breaks the input into 5-bit blocks, then
	 * represents those values with a 32-character alphabet.  The
	 * alphabet is 'A'=0 through 'Z'=25 and '2'=26 through '7'=31.
	 * Zero and One are skipped since they easily confused by humans
	 * for the letters 'O' and 'I'.  A padding character ('=') is used
	 * when 5-bit blocks don't fit neatly into 8-bit bytes.
	 */
	public static function Encode($String,$Pad=true) {
		$Result='';
		/* $Bit and $Byte are offsets into $String */
		$Byte=0;
		$Bit=0;

		$StringLen=strlen($String);
		while ($Byte<$StringLen) {
			$ByteValue=ord($String[$Byte]);

			if ($Byte<($StringLen-1))
				$NextByteValue=ord($String[$Byte+1]);
			else
				$NextByteValue=0;

			switch ($Bit) {
				case 0:
					$Result.=self::MapFiveBitsToChar($ByteValue>>3);
					$Bit=5;
					break;
				case 1:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue>>2));
					$Bit=6;
					break;
				case 2:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue>>1));
					$Bit=7;
					break;
				case 3:
					$Result.=self::MapFiveBitsToChar(31 & $ByteValue);
					$Bit=0;
					$Byte++;
					break;
				case 4:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue<<1 | (($NextByteValue & 128)>>7)));
					$Bit=1;
					$Byte++;
					break;
				case 5:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue<<2 | (($NextByteValue & 192)>>6)));
					$Bit=2;
					$Byte++;
					break;
				case 6:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue<<3 | (($NextByteValue & 224)>>5)));
					$Bit=3;
					$Byte++;
					break;
				case 7:
					$Result.=self::MapFiveBitsToChar(31 & ($ByteValue<<4 | (($NextByteValue & 240)>>4)));
					$Bit=4;
					$Byte++;
					break;
			}
		}
		if ($Pad) {
			switch ($StringLen%5) {
				case 0:
					/* No padding */
					break;
				case 1:
					$Result.='======';
					break;
				case 2:
					$Result.='====';
					break;
				case 3:
					$Result.='===';
					break;
				case 4:
					$Result.='=';
					break;
			}
		}
		return $Result;
	}

	public static function Decode($String,$IgnoreInvalid=true) {
		$Result='';
		$Next16Bits=0;
		$Next16Bitsi=0;
		$SawPad=false;

		$StrLen=strlen($String);
		for ($i=0; $i<$StrLen; $i++) {
			$CharVal=self::MapCharToFiveBits($String[$i],$IgnoreInvalid);
			if ($CharVal===self::PAD) {
				$SawPad=true;
			} else {
				if ($SawPad && !$IgnoreInvalid)
					throw new Exception('Padding encountered before the end of input.');
				$Next16Bits|=$CharVal<<(16-5-$Next16Bitsi);
				$Next16Bitsi+=5;
				if ($Next16Bitsi>=8) {
					$Result.=chr($Next16Bits>>8);
					$Next16Bits=0xFF00 & ($Next16Bits<<8);
					$Next16Bitsi-=8;
				}
			}
		}
		return $Result;
	}

	/* The mapping could be done as a lookup table, but
	 * that is tedious and doesn't do proper range checking.
	 */
	public static function MapFiveBitsToChar($FiveBits) {
		if ($FiveBits<0)
			throw new Exception('FiveBits value <0.');
		if ($FiveBits>31)
			throw new Exception('FiveBits value >31.');
		if ($FiveBits<26) {
			return chr(self::ASCII_A+$FiveBits);
		} else {
			return chr(self::ASCII_2+($FiveBits-26));
		}
	}

	public static function MapCharToFiveBits($Char,$IgnoreInvalid=true) {
		$CharVal=ord($Char);
		if ($Char===self::PAD)
			return self::PAD;
		if ($CharVal<self::ASCII_2) {
			if ($IgnoreInvalid)
				return null;
			else
				throw new Exception('Char value <2.');
		}
		if ($CharVal>self::ASCII_7 && $CharVal<self::ASCII_A) {
			if ($IgnoreInvalid)
				return null;
			else
				throw new Exception('Char value >9 and <A.');
		}
		if ($CharVal>self::ASCII_Z) {
			if ($IgnoreInvalid)
				return null;
			else
				throw new Exception('Char value >Z.');
		}

		if ($CharVal<self::ASCII_A) {
			return 26+($CharVal-self::ASCII_2);
		} else {
			return $CharVal-self::ASCII_A;
		}
	}
}

?>
