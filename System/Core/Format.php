<?php
/**
 *	OUTRAGEbot development
 */


class Format
{
	/**
	 *	Defining text formatting
	 */
	const
		Bold = "\002",
		Clear = "\017",
		Colour = "\003",
		CTCP = "\001",
		Inverse = "\026",
		Tab = "\011",
		Underline = "\037";
		
		
	/**
	 *	Defining colours
	 */
	const
		White = "\00300",
		Black = "\00301",
		DarkBlue = "\00302",
		DarkGreen = "\00303",
		Red = "\00304",
		Brown = "\00305",
		Purple = "\00306",
		Orange = "\00307",
		Yellow = "\00308",
		Green = "\00309",
		Teal = "\00310",
		LightBlue = "\00311",
		Blue = "\00312",
		Pink = "\00313",
		DarkGrey = "\00314",
		Grey = "\00315";
	
	
	/**
	 *	Defining backgrounds too!
	 */
	const
		Back_White = ',00',
		Back_Black = ',01',
		Back_DarkBlue = ',02',
		Back_DarkGreen = ',03',
		Back_Red = ',04',
		Back_Brown = ',05',
		Back_Purple = ',06',
		Back_Orange = ',07',
		Back_Yellow = ',08',
		Back_Green = ',09',
		Back_Teal = ',10',
		Back_LightBlue = ',11',
		Back_Blue = ',12',
		Back_Pink = ',13',
		Back_DarkGrey = ',14',
		Back_Grey = ',15';
		
	
	/**
	 *	Parser: get formatting reference
	 */
	static function getFormat($sFormat, $sNamespace = "")
	{
		$sFormat = strtolower($sFormat);
		$sFormat = ucwords($sFormat);
		$sNamespace = ucwords($sNamespace);
		
		if($sFormat == "/")
		{
			return self::Clear;
		}
		
		if(defined("self::{$sNamespace}{$sFormat}"))
		{
			return constant("self::{$sNamespace}{$sFormat}");
		}
		
		return "";
	}
	
	
	/**
	 *	Parser: decide if clear char is used
	 */
	static function useClearChar($sChar)
	{
		switch($sChar)
		{
			case "^":
			{
				return self::Clear;
			}
			default:
			{
				return "";
			}
		}
	}
}


/**
 *	The format function
 */
function Format($sInputString)
{
	while(preg_match("/(\*|\^)(.*?)(\*|\^)/", $sInputString, $aParts) != false)
	{
		$sOutputString = Format::useClearChar($aParts[1]);
		
		$aCodes = explode(":", $aParts[2], 2);
		
		if(!isset($aCodes[1]))
		{
			$sOutputString .= Format::getFormat($aCodes[0]);
		}
		else
		{
			$sOutputString .= Format::getFormat($aCodes[0]);
			$sOutputString .= Format::getFormat($aCodes[1], 'Back_');
		}
		
		$sOutputString .= Format::useClearChar($aParts[3]);
		$sInputString = str_replace($aParts[0], $sOutputString, $sInputString);
	}
	
	return $sInputString;
}