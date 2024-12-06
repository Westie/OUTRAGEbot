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
    public const CTCP = "\001";

    /**
     *	Text formatting modifiers (mIRC format)
     */
    public const BOLD = "\002";

    public const COLOUR = "\003";

    public const TAB = "\011";

    public const CLEAR = "\017";

    public const INVERSE = "\026";

    public const ITALIC = "\035";

    public const UNDERLINE = "\037";

    /**
     *	Foreground colour modifiers (mIRC format)
     */
    public const COLOUR_WHITE = "\00300";

    public const COLOUR_BLACK = "\00301";

    public const COLOUR_DARKBLUE = "\00302";

    public const COLOUR_DARKGREEN = "\00303";

    public const COLOUR_RED = "\00304";

    public const COLOUR_BROWN = "\00305";

    public const COLOUR_PURPLE = "\00306";

    public const COLOUR_ORANGE = "\00307";

    public const COLOUR_YELLOW = "\00308";

    public const COLOUR_GREEN = "\00309";

    public const COLOUR_TEAL = "\00310";

    public const COLOUR_LIGHTBLUE = "\00311";

    public const COLOUR_BLUE = "\00312";

    public const COLOUR_PINK = "\00313";

    public const COLOUR_DARKGREY = "\00314";

    public const COLOUR_GREY = "\00315";

    /**
     *	Background colour modifiers, note: must be used with a foreground
     *	colour modifier. (mIRC format)
     */
    public const BACKGROUND_WHITE = ',00';

    public const BACKGROUND_BLACK = ',01';

    public const BACKGROUND_DARKBLUE = ',02';

    public const BACKGROUND_DARKGREEN = ',03';

    public const BACKGROUND_RED = ',04';

    public const BACKGROUND_BROWN = ',05';

    public const BACKGROUND_PURPLE = ',06';

    public const BACKGROUND_ORANGE = ',07';

    public const BACKGROUND_YELLOW = ',08';

    public const BACKGROUND_GREEN = ',09';

    public const BACKGROUND_TEAL = ',10';

    public const BACKGROUND_LIGHTBLUE = ',11';

    public const BACKGROUND_BLUE = ',12';

    public const BACKGROUND_PINK = ',13';

    public const BACKGROUND_DARKGREY = ',14';

    public const BACKGROUND_GREY = ',15';
}
