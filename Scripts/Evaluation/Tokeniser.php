<?php
/**
*	OUTRAGEbot - PHP 5.3 based IRC bot
*
*	Author:		David Weston <westie@typefish.co.uk>
*
*	Version:        <version>
*	Git commit:     <commitHash>
*	Committed at:   <commitTime>
*
*	Licence:	http://www.typefish.co.uk/licences/
*/


class Tokeniser
{
	/**
	 *	Store our output
	 */
	private
		$sReturn,
		$aTokeniser;


	/**
	 *	Called when the tokeniser is loaded.
	 */
	public function __construct($sCode = null)
	{
		if($sCode == null)
		{
			return;
		}

		$this->__invoke($sCode);
	}


	/**
	 *	Tokenise and patch the code.
	 */
	public function __invoke($sCode)
	{
		$this->Analyse($sCode);
	}


	/**
	 *	Tokenise and patch the code.
	 */
	public function Analyse($sCode)
	{
		$this->aTokeniser = token_get_all('<?php '.$sCode.' ?>');

		$iIndex = 0;
		$iLength = count($this->aTokeniser);

		while($iIndex < $iLength)
		{
			# Get the cast type
			$mToken = $this->aTokeniser[$iIndex];
			$mPrevious = ($iIndex != 0 ? $this->aTokeniser[$iIndex - 1] : '');

			# Decide what patch we need to apply
			if(is_string($mToken))
			{
				if($mToken == '(' && $mPrevious[0] != T_STRING)
				{
					$this->Cast($iIndex, $this->aTokeniser);
				}
			}
			else
			{
				if($mToken[0] == T_STRING && $mPrevious[0] != T_OBJECT_OPERATOR)
				{
					if(!defined($mToken[1]) && !function_exists($mToken[1]) && $mToken[1] != "function")
					{
						$this->Method($iIndex, $this->aTokeniser);
					}
				}
			}

			++$iIndex;

			$iLength = count($this->aTokeniser);
		}
	}


	/**
	 *	Return the output as a string.
	 */
	public function getOutput()
	{
		return $this->__toString();
	}


	/**
	 *	Return the output as a string.
	 */
	public function __toString()
	{
		$sReturn = "";

		foreach($this->aTokeniser as $mToken)
		{
			if(is_string($mToken))
			{
				$sReturn .= $mToken;
			}
			else
			{
				$sReturn .= $mToken[1];
			}
		}

		return substr($sReturn, 6, -3);
	}


	/**
	 *	Called to cast something to a custom type/object.
	 */
	private function Cast(&$iIndex)
	{
		# Retrieve our cast.
		$iStart = $iIndex;
		$aToken = &$this->aTokeniser[++$iIndex];

		if(!is_array($aToken) || $aToken[0] != 307)
		{
			return count($this->aTokeniser);
		}

		$sCast = $aToken[1];

		$aToken = &$this->aTokeniser[++$iIndex];

		if($aToken != ')')
		{
			return;
		}

		# Get rid of whitespace to locate our variable.
		while($this->aTokeniser[++$iIndex][0] == 371);

		list($iType, $sObject, $iLine) = $this->aTokeniser[$iIndex];

		if($iType == T_STRING)
		{
			$iType = T_CONSTANT_ENCAPSED_STRING;
			$sObject = '"'.$sObject.'"';
		}

		# Find the end of our expression.
		while(!in_array($this->aTokeniser[++$iIndex][0], array(')', ';', T_OBJECT_OPERATOR)));

		# And finally, fix the array.
		$iSpliceLength = $iIndex - $iStart;

		array_splice($this->aTokeniser, $iStart, $iSpliceLength, array
		(
			array
			(
				T_VARIABLE,
				'$this',
				$iLine,
			),

			array
			(
				T_OBJECT_OPERATOR,
				'->',
				$iLine,
			),

			array
			(
				T_STRING,
				'to'.ucwords($sCast),
				$iLine,
			),

			'(',

			array
			(
				$iType,
				$sObject,
				$iLine,
			),

			')',
		));
	}


	private function Method(&$iIndex)
	{
		# Retrieve our cast.
		$iStart = $iIndex;

		list(, $sMethod, $iLine) = $this->aTokeniser[$iIndex];

		# Get rid of whitespace to locate our function.
		while($this->aTokeniser[++$iIndex][0] == 371);

		$sToken = $this->aTokeniser[$iIndex];

		if($sToken != '(')
		{
			return;
		}

		array_splice($this->aTokeniser, $iStart, 1, array
		(
			array
			(
				T_VARIABLE,
				'$this',
				$iLine,
			),

			array
			(
				T_OBJECT_OPERATOR,
				'->',
				$iLine,
			),

			array
			(
				T_STRING,
				$sMethod,
				$iLine,
			),
		));

		++$iIndex;
	}
}
