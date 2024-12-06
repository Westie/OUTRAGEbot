<?php
/**
 *	This class contains various text modifiers - for colours, CTCP, etc.
 */


namespace OUTRAGEbot\Format;


class Modifiers
{
	/**
	 *	Control modifiers
	 */
	const CTCP = "\001";
	
	
	/**
	 *	Text formatting modifiers (mIRC format)
	 */
	const BOLD = "\002";
	const COLOUR = "\003";
	const TAB = "\011";
	const CLEAR = "\017";
	const INVERSE = "\026";
	const ITALIC = "\035";
	const UNDERLINE = "\037";
	
	
	/**
	 *	Foreground colour modifiers (mIRC format)
	 */
	const COLOUR_WHITE = "\00300";
	const COLOUR_BLACK = "\00301";
	const COLOUR_DARKBLUE = "\00302";
	const COLOUR_DARKGREEN = "\00303";
	const COLOUR_RED = "\00304";
	const COLOUR_BROWN = "\00305";
	const COLOUR_PURPLE = "\00306";
	const COLOUR_ORANGE = "\00307";
	const COLOUR_YELLOW = "\00308";
	const COLOUR_GREEN = "\00309";
	const COLOUR_TEAL = "\00310";
	const COLOUR_LIGHTBLUE = "\00311";
	const COLOUR_BLUE = "\00312";
	const COLOUR_PINK = "\00313";
	const COLOUR_DARKGREY = "\00314";
	const COLOUR_GREY = "\00315";
	
	
	/**
	 *	Background colour modifiers, note: must be used with a foreground
	 *	colour modifier. (mIRC format)
	 */
	const BACKGROUND_WHITE = ",00";
	const BACKGROUND_BLACK = ",01";
	const BACKGROUND_DARKBLUE = ",02";
	const BACKGROUND_DARKGREEN = ",03";
	const BACKGROUND_RED = ",04";
	const BACKGROUND_BROWN = ",05";
	const BACKGROUND_PURPLE = ",06";
	const BACKGROUND_ORANGE = ",07";
	const BACKGROUND_YELLOW = ",08";
	const BACKGROUND_GREEN = ",09";
	const BACKGROUND_TEAL = ",10";
	const BACKGROUND_LIGHTBLUE = ",11";
	const BACKGROUND_BLUE = ",12";
	const BACKGROUND_PINK = ",13";
	const BACKGROUND_DARKGREY = ",14";
	const BACKGROUND_GREY = ",15";
}