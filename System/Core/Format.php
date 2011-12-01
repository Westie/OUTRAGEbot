<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     4a7dced0b3ef96338f36bc64bd40ed91063c3e01
 *	Committed at:   Thu Dec  1 22:49:57 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
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
		Italic = "\035",
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
	 *	The private stuff.
	 */
	private static
		$aConstants = null;


	/**
	 *	Parse the input string.
	 *
	 *	Simple tags:	{b}		Emboldens the string past that point.
	 *			{i}		Puts the text into italics.
	 *			{r}		Cancels all formatting and colours after that point.
	 *			{u}		Underlines the string past that point.
	 *			{v}		Inverts the background and foreground colours.
	 *
	 *	Colour tags:	{c:blue}	Colourises the text blue.
	 *			{c:blue:red}	Colourises the text blue, and the background red.
	 *
	 *	Sample: {b}Welcome to the {u}channel{u}!
	 */
	public static function parseInputString($sInputString)
	{
		if(self::$aConstants == null)
		{
			self::populateConstantList();
		}

		$sInputString = preg_replace_callback("/\{[biruv]\}/", array("Format", "parseSimpleTag"), $sInputString);
		$sInputString = preg_replace_callback("/\{c:(.*?)(:(.*?))?\}/", array("Format", "parseColourTag"), $sInputString);

		return $sInputString;
	}


	/**
	 *	Populates the internal cache of constants.
	 */
	private static function populateConstantList()
	{
		$pReflection = new ReflectionClass(__CLASS__);
		$aConstants = array_keys($pReflection->getConstants());

		foreach($aConstants as $sConstant)
		{
			self::$aConstants[strtolower($sConstant)] = $sConstant;
		}

		return;
	}


	/**
	 *	Returns the colour definition.
	 */
	private static function getColourConstant($sColour)
	{
		$sColour = strtolower($sColour);

		if(isset(self::$aConstants[$sColour]))
		{
			return constant("Format::".self::$aConstants[$sColour]);
		}

		return "";
	}


	/**
	 *	Parses the simple tags.
	 */
	private static function parseSimpleTag($aMatches)
	{
		$cTag = $aMatches[0][1];

		switch($cTag)
		{
			case 'b':
			{
				return Format::Bold;
			}

			case 'i':
			{
				return Format::Italic;
			}

			case 'r':
			{
				return Format::Clear;
			}

			case 'u':
			{
				return Format::Underline;
			}

			case 'v':
			{
				return Format::Inverse;
			}
		}

		return;
	}


	/**
	 *	Parse colour tags
	 */
	private static function parseColourTag($aMatches)
	{
		$iOption = count($aMatches);
		$sColour = self::getColourConstant($aMatches[1]);

		if($iOption == 4)
		{
			$sBack = self::getColourConstant('Back_'.$aMatches[3]);

			return $sColour.$sBack;
		}

		return $sColour;
	}
}


/**
 *	The format function
 */
function Format($sInputString)
{
	return Format::parseInputString($sInputString);
}
