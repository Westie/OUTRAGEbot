<?php

/**
 *	Format module for OUTRAG3bot.
 */

namespace OUTRAGEbot\Module\Modules;

use OUTRAGEbot\Module;

class Format extends Module\Template
{
    /**
     *	Defining text formatting
     */
    public const BOLD = "\002";

    public const CLEAR = "\017";

    public const COLOUR = "\003";

    public const CTCP = "\001";

    public const INVERSE = "\026";

    public const TAB = "\011";

    public const ITALIC = "\035";

    public const UNDERLINE = "\037";

    /**
     *	Defining colours
     */
    public const TEXT_WHITE = "\00300";

    public const TEXT_BLACK = "\00301";

    public const TEXT_DARK_BLUE = "\00302";

    public const TEXT_DARK_GREEN = "\00303";

    public const TEXT_RED = "\00304";

    public const TEXT_BROWN = "\00305";

    public const TEXT_PURPLE = "\00306";

    public const TEXT_ORANGE = "\00307";

    public const TEXT_YELLOW = "\00308";

    public const TEXT_GREEN = "\00309";

    public const TEXT_TEAL = "\00310";

    public const TEXT_LIGHT_BLUE = "\00311";

    public const TEXT_BLUE = "\00312";

    public const TEXT_PINK = "\00313";

    public const TEXT_DARK_GREY = "\00314";

    public const TEXT_GREY = "\00315";

    /**
     *	Defining backgrounds too!
     */
    public const BACK_WHITE = ',00';

    public const BACK_BLACK = ',01';

    public const BACK_DARK_BLUE = ',02';

    public const BACK_DARK_GREEN = ',03';

    public const BACK_RED = ',04';

    public const BACK_BROWN = ',05';

    public const BACK_PURPLE = ',06';

    public const BACK_ORANGE = ',07';

    public const BACK_YELLOW = ',08';

    public const BACK_GREEN = ',09';

    public const BACK_TEAL = ',10';

    public const BACK_LIGHT_BLUE = ',11';

    public const BACK_BLUE = ',12';

    public const BACK_PINK = ',13';

    public const BACK_DARK_GREY = ',14';

    public const BACK_GREY = ',15';

    /**
     *	Stores the cached versions of these constant names,
     *	such as lower case and stuff.
     */
    private $cache = [];

    /**
     *	Stores cached patterns.
     */
    private $patterns = [];

    /**
     *	Called when the module has been loaded into memory.
     */
    public function construct()
    {
        # automatically add stuff
        $reflection = new \ReflectionObject($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->getName() == 'construct') {
                continue;
            }

            if ($method->getDeclaringClass()->getName() == get_class($this)) {
                $this->introduceMethod($method->getName());
            }
        }

        $this->generateFormatCache();
        $this->generatePatternCache();
    }

    /**
     *	Strips the text of formatting.
     *
     *	@param string $input  String to be removed of only formatting markers.
     */
    public function stripFormat($context, $input)
    {
        return preg_replace("/[\002\017\001\026\001\037]/", '', $input);
    }

    /**
     *	Strips the text of colours.
     *
     *	@param string $input  String to be removed of only colour markers.
     */
    public function stripColour($context, $input)
    {
        return preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", '', $input);
    }

    /**
     *	Strips the text of formatting and colours.
     *
     *	@param string $input  String to be removed of all formatting and colour markers.
     */
    public function stripAll($context, $input)
    {
        return preg_replace("/[\002\017\001\026\001\037]/", '', preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", '', $input));
    }

    /**
     *	Provides a way to format certain strings with colour and other formatting markers.
     *
     *	@param string $input  String to be parsed.
     */
    public function format($context, $input)
    {
        foreach ($this->patterns as $pattern => $callback) {
            $input = preg_replace_callback($pattern, $callback, $input);
        }

        return $input;
    }

    /**
     *	This method is used to generate the format cache.
     */
    protected function generateFormatCache()
    {
        $this->cache =
        [
            'formatting' => [],
            'foreground' => [],
            'background' => [],
        ];

        $constants = (new \ReflectionObject($this))->getConstants();

        foreach ($constants as $constant => $value) {
            $matches = [];

            if (preg_match('/^BACK_(.*)$/', $constant, $matches)) {
                $constant = $matches[1];

                $this->cache['background'][strtolower($constant)] = $value;
                $this->cache['background'][strtolower(str_replace('_', '', $constant))] = $value;
            } elseif (preg_match('/^TEXT_(.*)$/', $constant, $matches)) {
                $constant = $matches[1];

                $this->cache['foreground'][strtolower($constant)] = $value;
                $this->cache['foreground'][strtolower(str_replace('_', '', $constant))] = $value;
            } else {
                $this->cache['formatting'][strtolower($constant)] = $value;
                $this->cache['formatting'][strtolower(str_replace('_', '', $constant))] = $value;
            }
        }

        return true;
    }

    /**
     *	Generates the pattern cache.
     */
    protected function generatePatternCache()
    {
        $reflection = new \ReflectionObject($this);

        $this->patterns = [];

        $this->patterns["/\{[biruv]\}/"] = $reflection->getMethod('parseInputSimpleTag')->getClosure($this);
        $this->patterns["/\{c:(.*?)(:(.*?))?\}/"] = $reflection->getMethod('parseInputColourTag')->getClosure($this);

        return true;
    }

    /**
     *	This helper method parses the simple tags - bolds, italics, that sort of thing.
     */
    protected function parseInputSimpleTag($matches)
    {
        $tag = $matches[0][1];

        switch ($tag) {
            case 'b':
                return self::BOLD;
                break;

            case 'i':
                return self::ITALIC;
                break;

            case 'r':
                return self::CLEAR;
                break;

            case 'u':
                return self::UNDERLINE;
                break;

            case 'v':
                return self::INVERSE;
                break;
        }

        return '';
    }

    /**
     *	This helper method parses the advanced tags - pretty much at this moment in time only the colour tag.
     */
    protected function parseInputColourTag($matches)
    {
        $foreground = strtolower($matches[1]);

        if (empty($this->cache['foreground'][$foreground])) {
            return '';
        }

        $colour = $this->cache['foreground'][$foreground];

        if (!empty($matches[3])) {
            $background = strtolower($matches[3]);

            if (!empty($this->cache['background'][$background])) {
                $colour .= $this->cache['background'][$background];
            }
        }

        return $colour;
    }
}
